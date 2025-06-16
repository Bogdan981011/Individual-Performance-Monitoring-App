import pygame
import re
import os
import uuid
import numpy as np
from PIL import Image
import gradio as gr
import math
import cv2
import sys
import asyncio
import random
from models import *
import json


"""
===================================================
               -- OBJ CLASSES --
===================================================
"""

# --- Player class with trails, speed, color ---
class Player:
    def __init__(self, id, start_pos, color=(0, 0, 255), speed=5, radius=15, label=None):
        self.id = id
        self.x, self.y = start_pos
        self.color = color
        self.speed = speed
        self.radius = radius
        self.label = label
        self.actions = []
        self.current_action_index = 0
        self.dx = 0
        self.dy = 0
        self.wait_frames = 0
        self.trail_enabled = False
        self.trail = []
        self.show_arrow = False
        self.max_speed = 10
        self.card_status = None  # Can be 'warning', 'yellow', 'red'
        self.fatigue = 0            # ranges from 0 to 100 (0=fresh, 100=exhausted)
        self.max_fatigue = 100
        self.fatigue_increase_rate = 0.1  # increase per frame while moving
        self.is_moving = False
        self.foul_targets = {}
        self.is_fouled = False


    def add_action(self, target, wait=0, color=None, speed=None):
        self.actions.append({
            "target": target,
            "wait": wait,
            "color": color if color else self.color,
            "speed": speed if speed else self.speed
        })

    def start_next_action(self):
        if self.current_action_index >= len(self.actions):
            return

        action = self.actions[self.current_action_index]

        # Handle dict-based action
        if isinstance(action, dict):
            action_type = action.get("type")

            # Possession-type logic
            if action_type == "possess_ball":
                self.wait_frames = action.get("wait", 0)
                self.dx = self.dy = 0
                return

            # Movement logic with "target"
            if "target" in action:
                self.wait_frames = action.get("wait", 0)
                self.color = action.get("color", self.color)
                requested_speed = action.get("speed", self.speed)
                self.speed = min(self.max_speed, requested_speed)

                target = action["target"]
                distance = math.hypot(target[0] - self.x, target[1] - self.y)
                steps = max(1, distance / self.speed)
                self.dx = (target[0] - self.x) / steps
                self.dy = (target[1] - self.y) / steps
                return

        # Tuple or string: no movement setup needed
        self.dx = self.dy = 0
        self.wait_frames = 0



    def update(self, ball=None, players=None):
        if self.is_fouled:
            self.dx = 0
            self.dy = 0
            self.is_moving = False
            return

        if self.current_action_index >= len(self.actions):
            return

        if self.wait_frames > 0:
            self.wait_frames -= 1
            return

        action = self.actions[self.current_action_index]

        # ✅ Handle "possess_ball" with wait as dict
        if isinstance(action, dict) and action.get("type") == "possess_ball":
            if self.wait_frames == 0:
                self.wait_frames = action.get("wait", 0)
            if self.wait_frames > 0:
                self.wait_frames -= 1
                return
            if ball:
                ball.possess(self)
                print(f"Player {self.label} possessed the ball at ({self.x}, {self.y})")
            self.current_action_index += 1
            self.start_next_action()
            return

        # ✅ Handle simple "possess_ball" (as string, backward compatibility)
        if isinstance(action, str):
            if action == "possess_ball" and ball:
                ball.possess(self)
                print(f"Player {self.label} possessed the ball at ({self.x}, {self.y})")
            self.current_action_index += 1
            self.start_next_action()
            return

        # ✅ Handle ball passes or kicks
        if isinstance(action, tuple):
            if action[0] == "pass_to" and ball and players:
                target_id = action[1]
                speed = action[2]
                wait = action[3]
                target_player = next((p for p in players if p.id == target_id), None)
                if target_player:
                    ball.pass_to(target_player, speed, wait)
            elif action[0] == "kick_to" and ball:
                target_point = action[1]
                speed = action[2]
                wait = action[3]
                if ball and ball.owner == self:
                    ball.kick_to(target_point, speed, wait)
            self.current_action_index += 1
            self.start_next_action()
            return

        # ✅ NORMAL MOVEMENT
        if action.get("target"):
            self.x += self.dx
            self.y += self.dy
            if self.trail_enabled:
                self.trail.append((int(self.x), int(self.y)))

            if abs(self.x - action["target"][0]) < abs(self.dx) + 1 and \
            abs(self.y - action["target"][1]) < abs(self.dy) + 1:
                self.x, self.y = action["target"]
                self.current_action_index += 1
                self.start_next_action()

        # fatigue system
        self.is_moving = self.wait_frames == 0
        if self.is_moving == True:
            # Movement = fatigue increases
            fatigue_delta = 0.05 * self.speed
            self.fatigue = min(self.max_fatigue, self.fatigue + fatigue_delta)
        if self.is_moving == False:
            # Idle = fatigue recovers
            self.fatigue = max(0, self.fatigue - 0.02*self.wait_frames)


        # After movement & fatigue update
        print(f"player pos : {(self.x, self.y)}")
        


    def draw(self, surface, font=None, ball=None):
        # trails
        if self.trail_enabled and len(self.trail) > 1:
            pygame.draw.lines(surface, self.color, False, self.trail, 2)

        # Halo glow if player has possession
        if ball and ball.owner == self:
            for r in range(self.radius + 10, self.radius + 25, 4):
                alpha = max(0, 100 - (r - self.radius - 10) * 10)
                halo_surface = pygame.Surface((r*2, r*2), pygame.SRCALPHA)
                pygame.draw.circle(halo_surface, (255, 215, 0, alpha), (r, r), r)
                surface.blit(halo_surface, (int(self.x - r), int(self.y - r)))

        # player circle
        pygame.draw.circle(surface, self.color, (int(self.x), int(self.y)), self.radius)

        # player tag
        if self.label and font:
            text = font.render(str(self.label), True, (0, 0, 0))
            surface.blit(text, (self.x - 10, self.y - 10))

        # arrow
        if self.show_arrow and (self.dx != 0 or self.dy != 0):
            # CONFIG: min and max arrow lengths in pixels
            min_arrow_length = 30
            max_arrow_length = 150
            scaling_factor = 4.0  # Adjusts how much speed affects arrow length

            # Compute desired arrow length based on speed
            raw_length = self.speed * scaling_factor
            arrow_length = max(min_arrow_length, min(max_arrow_length, raw_length))

            # Normalize the direction vector
            direction_length = math.hypot(self.dx, self.dy)
            unit_dx = self.dx / direction_length
            unit_dy = self.dy / direction_length

            # Tip of the arrow
            tip_x = self.x + unit_dx * arrow_length
            tip_y = self.y + unit_dy * arrow_length

            # Arrowhead shape
            angle = math.atan2(self.dy, self.dx)
            head_length = 10
            head_width = 6

            left = (
                tip_x - head_length * math.cos(angle) + head_width * math.sin(angle),
                tip_y - head_length * math.sin(angle) - head_width * math.cos(angle)
            )
            right = (
                tip_x - head_length * math.cos(angle) - head_width * math.sin(angle),
                tip_y - head_length * math.sin(angle) + head_width * math.cos(angle)
            )

            # Determine arrow color by speed
            if 1 <= self.speed <= 3:
                arrow_color = (255, 255, 255)       # Red
            elif 3 < self.speed <= 7:
                arrow_color = (255, 255, 50)     # Yellow
            elif 7 < self.speed <= 10:
                arrow_color = (200, 255, 50)       # Green
            else:
                arrow_color = (0, 0, 0)         # Default Black

            # Shaft and head
            pygame.draw.line(surface, arrow_color, (int(self.x), int(self.y)), (int(tip_x), int(tip_y)), 2)
            pygame.draw.polygon(surface, arrow_color, [(tip_x, tip_y), left, right])

            # Speed label positioned just past the arrowhead
            if font:
                label_offset = 12
                label_x = tip_x + label_offset * unit_dx
                label_y = tip_y + label_offset * unit_dy
                speed_text = font.render(f"{int(self.speed)}", True, arrow_color)
                surface.blit(speed_text, (label_x, label_y))

        # Draw card symbol if player has one
        if self.card_status:
            card_color = {
                "warning": (255, 165, 0),  # Orange
                "yellow": (255, 255, 0),   # Yellow
                "red": (255, 0, 0)         # Red
            }.get(self.card_status, (255, 255, 255))

            card_rect = pygame.Rect(self.x - 8, self.y - self.radius - 20, 16, 16)
            pygame.draw.rect(surface, card_color, card_rect)

            if font:
                symbol = {"warning": "W", "yellow": "Y", "red": "R"}[self.card_status]
                card_text = font.render(symbol, True, (0, 0, 0))
                surface.blit(card_text, (self.x - 6, self.y - self.radius - 18))

        # Fatigue bar position
        bar_width = 30
        bar_height = 5
        fatigue_ratio = self.fatigue / self.max_fatigue
        bar_x = self.x - bar_width // 2
        bar_y = self.y + self.radius + 5

        # Draw background
        pygame.draw.rect(surface, (100, 100, 100), (bar_x, bar_y, bar_width, bar_height))

        # Draw fatigue level (color from green to red)
        r = min(max(int(255 * fatigue_ratio), 0), 255)
        g = min(max(int(255 * (1 - fatigue_ratio)), 0), 255)
        fatigue_color = (r, g, 0)

        pygame.draw.rect(surface, fatigue_color, (bar_x, bar_y, int(bar_width * fatigue_ratio), bar_height))

        # 🔴 Red halo if player is fouled
        if self.is_fouled:
            pygame.draw.circle(surface, (255, 0, 0), (int(self.x), int(self.y)), self.radius + 6, 3)


class Ball:
    def __init__(self, start_pos):
        self.x, self.y = start_pos
        self.owner = None         # Player instance or None
        self.target = None        # (x, y) or Player
        self.speed = 10           # Movement speed when kicked or passed
        self.dx = 0
        self.dy = 0
        self.in_motion = False
        self.scored = False       # Flag to track if ball has scored
        self.out_of_bounds = False

    def possess(self, player):
        self.owner = player
        self.in_motion = False
        self.target = None
        self.x = player.x
        self.y = player.y
        self.scored = False      # Reset scored flag when ball is possessed

    def pass_to(self, target_player, speed=10, wait=0):
        self.owner = None
        self.target = target_player
        self.speed = speed
        self.wait = wait
        self.in_motion = True
        self.scored = False      # Reset scored flag when ball is passed
        self._calculate_trajectory((target_player.x, target_player.y))

    def kick_to(self, target_point, speed=15, wait=0):
        self.owner = None
        self.target = target_point
        self.speed = speed
        self.wait = wait
        self.in_motion = True
        self.scored = False      # Reset scored flag when ball is kicked
        self._calculate_trajectory(target_point)

    def _calculate_trajectory(self, target_pos):
        tx, ty = target_pos
        distance = math.hypot(tx - self.x, ty - self.y)
        steps = max(1, distance / self.speed)
        self.dx = (tx - self.x) / steps
        self.dy = (ty - self.y) / steps

    def update(self):
        if self.owner:
            print(f"Ball is possessed by Player {self.owner.label} at ({self.x}, {self.y})")
            self.x = self.owner.x
            self.y = self.owner.y
        elif self.in_motion:
            if self.wait > 0:
                self.wait -= 1
                return  # Wait before starting to move
            
            print(f"Ball is moving toward {self.target} from ({self.x:.1f}, {self.y:.1f})")
            self.x += self.dx
            self.y += self.dy

            # Check if ball has reached the target
            if isinstance(self.target, Player):
                tx, ty = self.target.x, self.target.y
            else:
                tx, ty = self.target

            if abs(self.x - tx) < abs(self.dx) + 1 and abs(self.y - ty) < abs(self.dy) + 1:
                self.x, self.y = tx, ty
                self.in_motion = False
                if isinstance(self.target, Player):
                    self.possess(self.target)
                    print("Ball possesed")
                else:
                    print("Ball arrived at target point.")

    def draw(self, surface):
        pygame.draw.circle(surface, (255, 255, 255), (int(self.x), int(self.y)), 6)
        pygame.draw.circle(surface, (0, 0, 0), (int(self.x), int(self.y)), 6, 1)  # outline


class Zone:
    def __init__(self, name, rect, color=(255, 200, 0), appear_at=0, disappear_at=None):
        self.name = name
        self.rect = rect  # (x, y, width, height)
        self.color = color
        self.appear_at = appear_at
        self.disappear_at = disappear_at

    def draw(self, surface, font, frame_count):
        if frame_count >= self.appear_at and (self.disappear_at is None or frame_count <= self.disappear_at):
            # Pulsation thickness: varies between 2 and 5
            pulse_thickness = 5 + int(2 * abs(math.sin(frame_count * 0.3)))
            pygame.draw.rect(surface, self.color, self.rect, pulse_thickness)
            if font:
                text = font.render(self.name, True, (0, 0, 0))
                surface.blit(text, (self.rect[0] + 5, self.rect[1] - 20))


"""
===================================================
                  -- TOOLS --
===================================================
"""

# --- Function to draw field lines ---
def draw_grid(surface, width, height, font):
    """
    Role : Draws a grid of useful lines of the field
    """
    # Line positions based on 800x600 canvas
    # Vertical (Y) lines
    horizontal_lines = [
    (40, "Touchline T"),              # Top of field
    (75, "5m T"),                    # ~5m from top
    (145, "15m T"),                  # ~15m from top
    (415, "15m B"),                  # ~15m from bottom
    (481, "5m B"),                   # ~5m from bottom
    (515, "Touchline B"),           # Bottom of field
    ]


    # Horizontal (X) lines
    vertical_lines = [
    (30, "End of the field L"),               # Left goal line
    (97, "Goal Line L"),               # Left goal line
    (160, "10m L"),                     # 5m from left
    (220, "20m L"),                    # 10m from left
    (280, "30m L"),                   # 22m from left
    (340, "40m L"),                   # 22m from left
    (400, "Halfway"),                # Midfield
    (460, "40m R"),                   # 22m from right
    (520, "30m R"),                   # 10m from right
    (580, "20m R"),                    # 5m from right
    (640, "10m R"),                    # 5m from right
    (705, "Goal Line R"),            # Right goal line
    (770, "End of the field R"),            # Right goal line
    ]

    grid_color = (255, 255, 0)
    label_color = (0, 0, 0)
    thickness = 2

    # Draw and label horizontal lines
    for y, label in horizontal_lines:
        pygame.draw.line(surface, grid_color, (0, y), (width, y), thickness)
        if font:
            text = font.render(label, True, label_color)
            surface.blit(text, (5, y - 15))

    # Draw and label vertical lines
    for x, label in vertical_lines:
        pygame.draw.line(surface, grid_color, (x, 0), (x, height), thickness)
        if font:
            text = font.render(label, True, label_color)
            surface.blit(text, (x + 5, 5))

# Collision checker
def check_collision(p1, p2):
    """
    Role : Checks if there's a collision between two players
    Params : (p1, p2) - player objects
    """
    dist = math.hypot(p1.x - p2.x, p1.y - p2.y)
    return dist <= (p1.radius + p2.radius)


def simulate_knock_out(ball, player):
    print(f"💥 Knock-out: {player.label} loses the ball due to foul!")

    # Try to kick in current movement direction
    dx, dy = player.dx, player.dy
    if dx == 0 and dy == 0:
        dx, dy = 1, 0  # default direction if idle

    norm = math.hypot(dx, dy)
    unit_dx, unit_dy = dx / norm, dy / norm

    # Optional: small random angle perturbation
    angle_offset = np.random.uniform(-0.3, 0.3)
    angle = math.atan2(unit_dy, unit_dx) + angle_offset
    unit_dx, unit_dy = math.cos(angle), math.sin(angle)

    # Determine knock-out target point
    distance = 40  # how far the ball flies
    target_x = player.x + unit_dx * distance
    target_y = player.y + unit_dy * distance

    ball.kick_to((target_x, target_y), speed=7, wait=0)


def resolve_collision(p1, p2, fouls=None, ball=None):
    """
    Role : 
        - assigns a card for the foul of a player
        - the fouled player drops the ball and its fatigue increases

    Params : 
        - p1 -- player object
        - p2 -- player object
        - fouls -- list of players to be fouled
        - ball -- ball object
    """
    # Vector between players
    dx = p2.x - p1.x
    dy = p2.y - p1.y
    distance = math.hypot(dx, dy)
    overlap = (p1.radius + p2.radius) - distance

    if overlap > 0 and distance != 0:
        # Normalize direction
        nx = dx / distance
        ny = dy / distance

        # Move each player away by half the overlap
        move_amount = overlap / 2
        p1.x -= nx * move_amount
        p1.y -= ny * move_amount
        p2.x += nx * move_amount
        p2.y += ny * move_amount

        # ✅ Manual Foul Detection
        if fouls is not None:
            if p2.id in p1.foul_targets:
                card = p1.foul_targets[p2.id]
                fouls.append((p1.label, p2.label, card))
                p2.is_fouled = True              # ✅ <- add this
                p2.fatigue += 0.5
                if card:
                    p1.card_status = card
                # 💥 Knock-out: p2 has the ball and was fouled
                if ball and ball.owner == p2:
                    simulate_knock_out(ball, p2)

            elif p1.id in p2.foul_targets:
                card = p2.foul_targets[p1.id]
                fouls.append((p2.label, p1.label, card))
                p1.is_fouled = True              # ✅ <- add this
                p1.fatigue += 0.5
                if card:
                    p2.card_status = card
                # 💥 Knock-out: p1 has the ball and was fouled
                if ball and ball.owner == p1:
                    simulate_knock_out(ball, p1)


def check_goal(player, ball, last_scoring_type=None):
    """
    Check if a try, conversion, penalty, or drop goal has been scored
    Returns: (team, points, type) where:
        team: 1 for team 1, 2 for team 2
        points: 5 for try, 2 for conversion, 3 for penalty/drop goal
        type: 'try', 'conversion', 'penalty', 'drop_goal'
    """
    # If ball has already scored, don't check again
    if ball.scored:
        return (0, 0, '')

    # Field positions
    left_goal_x = 97    # Left goal line
    right_goal_x = 705  # Right goal line
    
    # Goal posts positions (y-coordinates)
    goal_posts_y = [250, 300]  # Goal posts are between these y-coordinates
    
    # Check if ball is in in-goal area (try)
    if ball.x <= left_goal_x and ball.owner == player and player.x <= left_goal_x:
        ball.scored = True
        return (2, 5, 'try')  # Team 2 scores a try
    if ball.x >= right_goal_x and ball.owner == player and player.x >= right_goal_x:
        ball.scored = True
        return (1, 5, 'try')  # Team 1 scores a try
    
    # Check if ball crosses goal line and is between goal posts
    if ball.y >= goal_posts_y[0] and ball.y <= goal_posts_y[1]:
        if ball.x <= left_goal_x:
            if last_scoring_type == 'try':
                ball.scored = True
                return (2, 2, 'conversion')  # Team 2 scores a conversion
            ball.scored = True
            return (2, 3, 'penalty or drop goal')  # Team 2 scores a penalty
        elif ball.x >= right_goal_x:
            if last_scoring_type == 'try':
                ball.scored = True
                return (1, 2, 'conversion')  # Team 1 scores a conversion
            ball.scored = True
            return (1, 3, 'penalty or drop goal')  # Team 1 scores a penalty
    
    return (0, 0, '')


def generate_players(n, 
                     start_area,       # ((xmin, xmax), (ymin, ymax))
                     template=None):
    """
    Generate `n` Player objects whose parameters all
    default to those in `template`, but with randomized
    start positions inside `start_area`.
    """
    xmin, xmax = start_area[0]
    ymin, ymax = start_area[1]

    # default template
    defaults = {
        "color": (0, 0, 255),
        "speed": 5,
        "radius": 15,
        "label_prefix": "P"
    }
    if template:
        defaults.update(template)
    
    players = []
    for i in range(1, n+1):
        # pick a random start position
        x = random.uniform(xmin, xmax)
        y = random.uniform(ymin, ymax)

        # create the player
        p = Player(
            id=i,
            start_pos=(x, y),
            color=defaults["color"],
            speed=defaults["speed"],
            radius=defaults["radius"],
            label=f"{defaults['label_prefix']}{i}"
        )
        players.append(p)
    return players


def check_ball_out(ball, field_bounds):
    """
    Detects if the ball has gone out of bounds and updates its state.

    Args:
        ball (Ball): The ball object.
        field_bounds (dict): Dictionary with keys 'left', 'right', 'top', 'bottom'.

    Returns:
        bool: True if ball went out, False otherwise.
    """
    out = (
        ball.x < field_bounds['left'] or
        ball.x > field_bounds['right'] or
        ball.y < field_bounds['top'] or
        ball.y > field_bounds['bottom']
    )

    if out and not ball.out_of_bounds:
        ball.in_motion = False
        ball.owner = None
        ball.out_of_bounds = True
        print("🏉 Ball went out of bounds!")
        return True

    ball.out_of_bounds = False
    return False


"""
===================================================
    -- INSTRUCTION --> PLAYER OBJ CREATION --
===================================================
"""

# --- Updated parser with trail support ---
def text_input_to_players_with_ball(text):
    phase_players = {}
    phase_balls = {}
    phase_zones = {}
    current_phase = 1
    phase_players[current_phase] = []
    players = []
    reset_frames = []
    lines = text.strip().splitlines()
    current_player = None
    player_id = 1

    for line in lines:
        form_match = re.match(
            r'Generate\s+(\d+)\s+players in formation\s+(\w+)'
            r'(?:\s+from\s+\((\d+),(\d+)\)\s+to\s+\((\d+),(\d+)\))?'
            r'(?:\s+center\((\d+),(\d+)\)\s+radius\((\d+)\))?'
            r'(?:\s+color\((\d+),(\d+),(\d+)\))?'
            r'(?:\s+speed\s+(\d+))?'
            r'(?:\s+prefix\(([^)]+)\))?',
            line, re.IGNORECASE
        )
        line = line.strip()

        if form_match:
            # unpack ALL 14 groups
            (n_s, shape,
            fx1, fy1, fx2, fy2,
            cx, cy, rad,
            cr, cg, cb,
            spd, pref) = form_match.groups()

            n = int(n_s)
            color = (int(cr), int(cg), int(cb)) if cr else (0, 0, 255)
            speed = int(spd) if spd else 5
            prefix = pref or "P"

            positions = []
            shape = shape.lower()

            if shape == "line" and fx1:
                # evenly space along the segment
                x1, y1, x2, y2 = map(int, (fx1, fy1, fx2, fy2))
                for i in range(n):
                    t = i / (n - 1) if n > 1 else 0
                    px = x1 + t * (x2 - x1)
                    py = y1 + t * (y2 - y1)
                    positions.append((px, py))

            elif shape == "circle" and cx:
                # evenly space on the circumference
                cx, cy, r = map(int, (cx, cy, rad))
                for i in range(n):
                    theta = 2 * math.pi * i / n
                    px = cx + r * math.cos(theta)
                    py = cy + r * math.sin(theta)
                    positions.append((px, py))

            else:
                # unsupported formation or missing params
                continue

            # now create the players
            for i, (px, py) in enumerate(positions, start=1):
                pid = player_id
                player_id += 1
                p = Player(
                    id=pid,
                    start_pos=(px, py),
                    color=color,
                    speed=speed,
                    radius=15,                    # or pull from a default variable
                    label=f"{prefix}{i}"
                )
                players.append(p)
                phase_players[current_phase].append(p)

            continue

        # ✅ RESET logic on phase change
        if line.lower().startswith("resetatframe"):
            match = re.search(r'resetatframe\s+(\d+)', line, re.IGNORECASE)
            if match:
                reset_frames.append(int(match.group(1)))

        if line.lower().startswith("phase"):
            match = re.search(r'phase\s+(\d+)', line, re.IGNORECASE)
            if match:
                current_phase = int(match.group(1))
                if current_phase not in phase_players:
                    phase_players[current_phase] = []


        if not line or line.lower().startswith("group"):
            continue

        if line.lower().startswith("ball at"):
            match = re.search(r'\((\d+),\s*(\d+)\)', line)
            if match:
                bx, by = int(match.group(1)), int(match.group(2))
                phase_balls[current_phase] = Ball((bx, by))
            continue

        if line.lower().startswith("player"):
            m = re.match(r'Player\s+([^:]+):', line, re.IGNORECASE)
            if m:
                label_txt = m.group(1).strip()
                # try to find a generated player with that label
                existing = next((p for p in players if p.label == label_txt), None)
                if existing:
                    current_player = existing
                else:
                    # no existing player → create a fresh one
                    pid = player_id
                    player_id += 1
                    current_player = Player(
                        id=pid,
                        start_pos=(0,0),
                        label=label_txt
                    )
                    players.append(current_player)
                    phase_players[current_phase].append(current_player)

        elif line.startswith("- at"):
            match = re.search(r'\((\d+),\s*(\d+)\)', line)
            if match:
                current_player.x = int(match.group(1))
                current_player.y = int(match.group(2))

        elif line.startswith("- label"):
            label_match = re.search(r'label\s+(.*)', line)
            if label_match:
                current_player.label = label_match.group(1).strip()

        elif line.startswith("- trails"):
            if "true" in line.lower():
                current_player.trail_enabled = True
            elif "false" in line.lower():
                current_player.trail_enabled = False

        elif line.startswith("- arrow"):
            if "true" in line.lower():
                current_player.show_arrow = True
            elif "false" in line.lower():
                current_player.show_arrow = False

        elif line.startswith("- card"):
            if "warning" in line.lower():
                current_player.card_status = "warning"
            elif "yellow" in line.lower():
                current_player.card_status = "yellow"
            elif "red" in line.lower():
                current_player.card_status = "red"

        elif line.startswith("- max_speed"):
            match = re.search(r'max_speed\s+(\d+)', line)
            if match:
                current_player.max_speed = int(match.group(1))

        elif line.startswith("- move to"):
            move_match = re.search(r'move to\s+\((\d+),\s*(\d+)\),\s*wait\s+(\d+)', line)
            color_match = re.search(r'color\s+\((\d+),\s*(\d+),\s*(\d+)\)', line)
            speed_match = re.search(r'speed\s+(\d+)', line)

            if move_match:
                tx = int(move_match.group(1))
                ty = int(move_match.group(2))
                wait = int(move_match.group(3))

                color = current_player.color
                if color_match:
                    color = (int(color_match.group(1)), int(color_match.group(2)), int(color_match.group(3)))

                speed = current_player.speed
                if speed_match:
                    speed = int(speed_match.group(1))

                current_player.add_action((tx, ty), wait, color, speed)

        elif line.startswith("- possess ball"):
            wait_match = re.search(r'wait\s+(\d+)', line)
            wait = int(wait_match.group(1)) if wait_match else 0
            current_player.actions.append({"type": "possess_ball", "wait": wait})

        elif line.startswith("- pass to"):
            match = re.search(r'Player\s+(\d+)', line, re.IGNORECASE)
            speed_match = re.search(r'speed\s+(\d+)', line)
            wait_match = re.search(r'wait\s+(\d+)', line)
            if match:
                target_id = int(match.group(1))
                speed = int(speed_match.group(1)) if speed_match else 10
                wait = int(wait_match.group(1)) if wait_match else 0
                current_player.actions.append(("pass_to", target_id, speed, wait))

        elif line.startswith("- kick to"):
            match = re.search(r'\((\d+),\s*(\d+)\)', line)
            speed_match = re.search(r'speed\s+(\d+)', line)
            wait_match = re.search(r'wait\s+(\d+)', line)
            if match:
                tx, ty = int(match.group(1)), int(match.group(2))
                speed = int(speed_match.group(1)) if speed_match else 15
                wait = int(wait_match.group(1)) if wait_match else 0
                current_player.actions.append(("kick_to", (tx, ty), speed, wait))

        elif line.startswith("- zone"):
            # Example: - zone name DangerZone at (100, 100) size (200, 100) appear 10 disappear 100
            match = re.search(r'name\s+(\w+).*?at\s+\((\d+),\s*(\d+)\).*?size\s+\((\d+),\s*(\d+)\)', line)
            appear = re.search(r'appear\s+(\d+)', line)
            disappear = re.search(r'disappear\s+(\d+)', line)

            if match:
                name = match.group(1)
                x, y = int(match.group(2)), int(match.group(3))
                w, h = int(match.group(4)), int(match.group(5))
                appear_at = int(appear.group(1)) if appear else 0
                disappear_at = int(disappear.group(1)) if disappear else None

                zone = Zone(name, (x, y, w, h), appear_at=appear_at, disappear_at=disappear_at)

                if current_phase not in phase_zones:
                    phase_zones[current_phase] = []
                phase_zones[current_phase].append(zone)


        elif line.startswith("- foul on"):
            match = re.search(r'Player\s+(\d+)', line, re.IGNORECASE)
            card_match = re.search(r'card\s+(warning|yellow|red)', line, re.IGNORECASE)
            if match:
                target_id = int(match.group(1))
                card_type = card_match.group(1).lower() if card_match else None
                current_player.foul_targets[target_id] = card_type


    for p in players:
        if p.actions:
            p.start_next_action()        


    return phase_players, phase_balls, phase_zones, reset_frames


"""
===================================================
    -- INSTRUCTION --> PLAYER OBJ ANIMATION --
===================================================

** text_input_to_players_with_ball() ** used for obj creation

===================================================
"""

def create_video_animation(text_input, grid=False, duration=200):
    width, height = 800, 600
    fps = 30
    filename = f"animation_{uuid.uuid4().hex}.mp4"
    os.makedirs("outputs", exist_ok=True)
    output_path = os.path.join("outputs", filename)

    pygame.init()
    screen = pygame.Surface((width, height))
    font = pygame.font.SysFont(None, 24)
    score_font = pygame.font.SysFont(None, 48)  # Larger font for score

    # Initialize scores and game state
    team1_score = 0
    team2_score = 0
    last_goal_frame = -100  # Track last goal for celebration effect
    last_out_frame = -100
    last_scoring_type = None
    last_scoring_type_display = None  # New variable to maintain scoring type for display
    conversion_attempt = False
    conversion_team = None

    try:
        bg_image = pygame.image.load("rugby_field.jpg")
        bg_image = pygame.transform.scale(bg_image, (width, height))
    except:
        bg_image = None

    current_phase = 1

    phase_players, phase_balls, phase_zones, reset_frames = text_input_to_players_with_ball(text_input)
    players = phase_players.get(current_phase, [])
    ball = phase_balls.get(current_phase, Ball((0, 0)))
    zones = phase_zones.get(current_phase, [])

    fourcc = cv2.VideoWriter_fourcc(*'avc1')  # H.264 codec
    out = cv2.VideoWriter(output_path, fourcc, fps, (width, height))

    for frame in range(duration):

        if bg_image:
            screen.blit(bg_image, (0, 0))
        else:
            screen.fill((255, 255, 255))

        if grid:
            draw_grid(screen, width, height, font)

        for zone in zones:
            zone.draw(screen, font, frame)

        if frame in reset_frames:
            current_phase += 1
            players = phase_players.get(current_phase, [])
            ball = phase_balls.get(current_phase, Ball((0, 0)))
            zones = phase_zones.get(current_phase, [])

            ball.out_of_bounds = False
            ball.in_motion = False
            ball.owner = None

            for p in players:
                p.start_next_action()

        for p in players:
            p.is_fouled = False  # Reset foul flag each frame
            p.update(ball=ball, players=players)

        # ✅ COLLISION DETECTION
        collisions = []
        fouls = []

        for i in range(len(players)):
            for j in range(i + 1, len(players)):
                if check_collision(players[i], players[j]):
                    cx = (players[i].x + players[j].x) // 2
                    cy = (players[i].y + players[j].y) // 2
                    collisions.append((cx, cy))
                    resolve_collision(players[i], players[j], fouls=fouls, ball=ball)

        for f1, f2, card in fouls:
            print(f"⚠️ Foul committed by {f1} on {f2}, card {card}")

        for p in players:
            p.draw(screen, font, ball)
            # check for scoring
            team, points, scoring_type = check_goal(p, ball, last_scoring_type)
            if team and frame - last_goal_frame > 90:  # Prevent multiple goals in quick succession
                if team == 1:
                    team1_score += points
                    print(f"🎯 Team 1 scores {points} points! ({scoring_type})")
                else:
                    team2_score += points
                    print(f"🎯 Team 2 scores {points} points! ({scoring_type})")
                
                last_goal_frame = frame
                last_scoring_type = scoring_type
                last_scoring_type_display = scoring_type  # Store the scoring type for display
                
                # Handle conversion attempt after try
                if scoring_type == 'try':
                    conversion_attempt = True
                    conversion_team = team
                elif scoring_type == 'conversion':
                    conversion_attempt = False
                    conversion_team = None
                
                ball.in_motion = False
                ball.owner = None
                break  # Exit the loop after a score is detected

        ball.update()

        if check_ball_out(ball, {
            'left': 30,
            'right': 770,
            'top': 40,
            'bottom': 525
        }):
            last_out_frame = frame

        ball.draw(screen)

        # ✅ DRAW COLLISION SYMBOLS
        for cx, cy in collisions:
            pygame.draw.circle(screen, (255, 0, 0), (int(cx), int(cy)), 10, 2)
            pygame.draw.line(screen, (255, 0, 0), (cx - 5, cy - 5), (cx + 5, cy + 5), 2)
            pygame.draw.line(screen, (255, 0, 0), (cx - 5, cy + 5), (cx + 5, cy - 5), 2)

        # Draw score with team names
        score_text = f"{team1_score} - {team2_score}"
        score_surface = score_font.render(score_text, True, (255, 255, 255))
        score_rect = score_surface.get_rect(center=(width//2, 30))
        
        # Draw semi-transparent background for score
        score_bg = pygame.Surface((score_rect.width + 20, score_rect.height + 10))
        score_bg.set_alpha(128)
        score_bg.fill((0, 0, 0))
        screen.blit(score_bg, (score_rect.x - 10, score_rect.y - 5))
        
        # Draw score text
        screen.blit(score_surface, score_rect)

        # Draw scoring celebration effect
        if frame - last_goal_frame < 90:  # Show celebration for 3 seconds (90 frames)
            celebration_text = f"{last_scoring_type_display}!".upper() if frame - last_goal_frame < 45 else ""  # Show text for 1.5 seconds
            celebration_surface = score_font.render(celebration_text, True, (255, 215, 0))  # Gold color
            celebration_rect = celebration_surface.get_rect(center=(width//2, 80))
            screen.blit(celebration_surface, celebration_rect)

        # Show "OUT!" if ball went out recently
        if frame - last_out_frame < 90:  # Show for 3 seconds
            out_text = "OUT!"
            out_surface = score_font.render(out_text, True, (255, 0, 0))  # Red color
            out_rect = out_surface.get_rect(center=(width//2, 120))

            # Optional: dark background behind text
            out_bg = pygame.Surface((out_rect.width + 20, out_rect.height + 10))
            out_bg.set_alpha(160)
            out_bg.fill((0, 0, 0))
            screen.blit(out_bg, (out_rect.x - 10, out_rect.y - 5))

            screen.blit(out_surface, out_rect)


        # Draw conversion attempt indicator
        if conversion_attempt:
            conv_text = f"Conversion attempt for Team {conversion_team}"
            conv_surface = font.render(conv_text, True, (255, 255, 255))
            conv_rect = conv_surface.get_rect(center=(width//2, 60))
            screen.blit(conv_surface, conv_rect)

        # Convert to BGR for OpenCV
        arr = pygame.surfarray.array3d(screen)
        frame_bgr = cv2.cvtColor(np.transpose(arr, (1, 0, 2)), cv2.COLOR_RGB2BGR)
        out.write(frame_bgr)

    out.release()
    pygame.quit()
    return output_path


"""
===================================================
              -- GRADIO SYSTEM --
===================================================
"""

# --- Gradio Interface ---
def generate_multi_action_animation(user_input, duration):

    fps = 30
    total_seconds = duration // fps
    minute = total_seconds // 60
    second = total_seconds % 60
    half = "First Half" if minute < 40 else "Second Half"
    time_label = f"⏱️ {half} – {minute:02d}:{second:02d}"

    video_path = create_video_animation(user_input, grid=False, duration=duration)
    return video_path, time_label


models = {
    "codellama": chat_with_codellama,
    "tinyllama": chat_with_tinyllama,
    "gemma": chat_with_gemma,
    "phi": chat_with_phi,
}

legend = """
    ### 🏉 Legend

    - 🟥R **Red Card** – Expulsion  
    - 🟨Y **Yellow Card** – Temporary exclusion  
    - 🟧W **Warning** – Pre-card warning

    ---

    - **Fatigue Bar** (Below Player):
    - ⬜⬜⬜ = Empty / Fresh
    - 🟩⬜⬜ Green → **Fresh**
    - 🟨🟨⬜ Yellow → **Moderate fatigue**
    - 🟥🟥🟥 Red → **Exhausted**

    ---

    - **Speed Arrow** (➤ direction indicator):
    - ⚪ **White** ➤ **Slow speed** (1–3)
    - 🟡 **Yellow** ➤ **Moderate speed** (4–7)
    - 🟥 **Red** ➤ **High speed** (8–10)
    """

def _on_send(message, history, model, prompt):
    fn = models[model]
    return fn(message, history, prompt)

dsl_commands = {
    "player_block": "Player <name>:",
    "player_position": "- at (x, y)",
    "player_label": "- label <string>",
    "player_color": "- color (r, g, b)",
    "player_move": "- move to (x, y), wait <int>, speed <int>, color (r, g, b)",
    "player_possess_ball": "- possess ball [wait <int>]",
    "player_pass": "- pass to Player <id>, wait <int>, speed <int>",
    "player_kick": "- kick to (x, y), wait <int>, speed <int>",
    "player_trail": "- trails true/false",
    "player_arrow": "- arrow true/false",
    "player_card": "- card warning/yellow/red",
    "player_max_speed": "- max_speed <int>",
    "player_foul": "- foul on Player <id> card <warning|yellow|red>",

    "formation_generate": "Generate <n> players in formation <line|circle> from (x1, y1) to (x2, y2) center(x, y) radius(r) color(r, g, b) speed <int> prefix(name)",

    "ball_block": "Ball:",
    "ball_at": "- at (x, y)",

    "zone_define": "- zone name <label> at (x, y) size (w, h) appear <int> disappear <int>",

    "phase_header": "Phase <n>",
    "reset_frame": "resetAtFrame <int>",
}





system_prompt = f"""

VOUS ÊTES : un parseur de tactiques de rugby strict et infaillible.  
VOTRE UNIQUE TÂCHE est de convertir une description en français d’un jeu en notre DSL d’animation.  

#### RÈGLES INDISCUTABLES  
1. **Sortie brute uniquement** : renvoyez **exclusivement** des lignes DSL, une commande par ligne, sans explication, sans préfixe, sans guillemets, sans bloc de code (autre que celui-ci pour le JSON).  
2. **Ne produisez aucun code** : pas de ```python```, pas de balises Markdown, pas de JSON en sortie — seulement du DSL.  
3. **Respectez à la lettre le schéma** suivant des commandes autorisées : {dsl_commands}


EXAMPLES:
1) Demande: Je veux un joueur qui bouge sur le terrain 
   Réponse:
        Player 1:
        - at (200, 200)
        - move to (400, 400), wait 5

2) Demande: Donne moi un joueur qui possede le ballon
   Réponse:
        Player 1:
        - at (300, 400)
        - possess ball

Maintenant, convertissez la consigne suivante en DSL :

"""



with gr.Blocks() as interface:
    gr.Markdown("# Rugby Animation & Player System")

    with gr.Row():
        with gr.Column(scale=2):
            gr.Markdown("## 🗨️ LLM Chat")
            model_choice = gr.Dropdown(list(models.keys()), value="codellama", label="Model")
            system_prompt = gr.Textbox(
                label="System prompt (DSL spec)", placeholder="You are a rugby tactics parser…",
                lines=2, value=system_prompt
            )
            chatbot = gr.Chatbot(label="Conversation")
            state = gr.State([])

            user_input_chat = gr.Textbox(placeholder="Type tactics or parser snippet…", label="User")
            send_btn = gr.Button("Send")
            send_btn.click(
                _on_send,
                inputs=[user_input_chat, state, model_choice, system_prompt],
                outputs=[chatbot, state]
            )

        with gr.Column(scale=3):
            gr.Markdown("## ▶️ Animation Controls")
            input_text      = gr.Textbox(label="Player Instructions (DSL)", lines=10)
            use_llm         = gr.Checkbox(label="Use LLM to parse above text", value=False)
            duration_slider = gr.Slider(50, 1000, step=50, label="Frames", value=300)
            time_display    = gr.Markdown("**⏱️ First Half – 00:00**", elem_id="time_display")
            video_output    = gr.Video(label="🏉 Animation")
            legend_output   = gr.Markdown(legend)

            generate_btn = gr.Button("Generate Animation")

            def on_generate(dsl_or_free, use_llm_flag, frames, llm_hist, prompt, model):
                # If LLM parsing enabled, get the last assistant reply as DSL
                if use_llm_flag and llm_hist:
                    # last reply is the last tuple’s second element
                    dsl = llm_hist[-1][1]
                else:
                    dsl = dsl_or_free
                video, time_lbl = generate_multi_action_animation(dsl, frames)
                return video, time_lbl

            generate_btn.click(
                on_generate,
                inputs=[input_text, use_llm, duration_slider, state, system_prompt, model_choice],
                outputs=[video_output, time_display]
            )

    if sys.platform.startswith('win'):
        asyncio.set_event_loop_policy(asyncio.WindowsSelectorEventLoopPolicy())

    interface.launch()

"""
what i have : 
    - players
    - color changing system for players while moving
    - trails (on/off)
    - background image
    - grid lines (on/off)
    - colored arrows that show the player direction and speed (on/off)
        * make green more visible (***)
    - ball logics (passes, kicks, posession, picking up)
    - highlight the zones where players will move in
    - zoom/pause buttons and play bar
    - card system (warning, yellow, red)
    - fatigue system (dependant by speed, recovery when standing)
        * specify the fatigue level of the player (***)
    - legend
        * update legend (collisions and foul)
    - time system (halfs, minutes)
        * specify a certain time for the generated phase (***)
    - collisions
    - phase construction (resetting the position for players, zones, balls)
    - showing score, scoring out system
    - generating groups of player in formations (lines, circles)
    

    ** Fri **
    - review all the small points
    - give a rag to a llm
    - link the llm with the animation system
"""
