import pygame
import re
import os
import uuid
import numpy as np
from PIL import Image
import imageio
import gradio as gr

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

    def add_action(self, target, wait=0, color=None, speed=None):
        self.actions.append({
            "target": target,
            "wait": wait,
            "color": color if color else self.color,
            "speed": speed if speed else self.speed
        })

    def start_next_action(self):
        if self.current_action_index < len(self.actions):
            action = self.actions[self.current_action_index]
            self.wait_frames = action["wait"]
            self.color = action.get("color", self.color)
            self.speed = action.get("speed", self.speed)
            target = action["target"]
            if target:
                distance = ((target[0] - self.x) ** 2 + (target[1] - self.y) ** 2) ** 0.5
                steps = max(1, distance / self.speed)
                self.dx = (target[0] - self.x) / steps
                self.dy = (target[1] - self.y) / steps
            else:
                self.dx = self.dy = 0

    def update(self):
        if self.current_action_index >= len(self.actions):
            return
        if self.wait_frames > 0:
            self.wait_frames -= 1
            return
        if self.actions[self.current_action_index]["target"]:
            self.x += self.dx
            self.y += self.dy
            if self.trail_enabled:
                self.trail.append((int(self.x), int(self.y)))
            # Check if target reached approximately
            if abs(self.x - self.actions[self.current_action_index]["target"][0]) < abs(self.dx) + 1 and \
               abs(self.y - self.actions[self.current_action_index]["target"][1]) < abs(self.dy) + 1:
                self.x, self.y = self.actions[self.current_action_index]["target"]
                self.current_action_index += 1
                self.start_next_action()

    def draw(self, surface, font=None):
        if self.trail_enabled and len(self.trail) > 1:
            pygame.draw.lines(surface, self.color, False, self.trail, 2)
        pygame.draw.circle(surface, self.color, (int(self.x), int(self.y)), self.radius)
        if self.label and font:
            text = font.render(str(self.label), True, (0, 0, 0))
            surface.blit(text, (self.x - 10, self.y - 10))

# --- Function to draw field lines ---
def draw_field_lines_with_labels(surface, width, height, font):
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
    (30, "Goal Line L"),               # Left goal line
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
    (770, "Goal Line R"),            # Right goal line
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


# --- Updated parser with trail support ---
def parse_input_to_multi_action_players(text):
    players = []
    lines = text.strip().splitlines()
    current_player = None
    player_id = 1

    for line in lines:
        line = line.strip()
        if not line or line.lower().startswith("group"):
            continue

        if line.lower().startswith("player"):
            match = re.match(r'Player\s+(\d+):', line, re.IGNORECASE)
            if match:
                current_player = Player(id=player_id, start_pos=(0, 0), label=f"P{player_id}")
                players.append(current_player)
                player_id += 1

        elif line.startswith("- at"):
            match = re.search(r'\((\d+),\s*(\d+)\)', line)
            if match:
                current_player.x = int(match.group(1))
                current_player.y = int(match.group(2))

        elif line.startswith("- trails"):
            if "true" in line.lower():
                current_player.trail_enabled = True
            elif "false" in line.lower():
                current_player.trail_enabled = False

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

    for p in players:
        if p.actions:
            p.start_next_action()
    return players

# --- Render GIF using Pygame offscreen ---
def create_gif_multi_action(text_input, grid=False):
    width, height = 800, 600
    duration = 90
    filename = f"animation_{uuid.uuid4().hex}.gif"
    os.makedirs("outputs", exist_ok=True)
    output_path = os.path.join("outputs", filename)

    pygame.init()
    screen = pygame.Surface((width, height))
    font = pygame.font.SysFont(None, 24)
    images = []

    # Load background
    try:
        bg_image = pygame.image.load("rugby_field.jpg")
        bg_image = pygame.transform.scale(bg_image, (width, height))
    except Exception as e:
        print("Failed to load background image:", e)
        bg_image = None

    players = parse_input_to_multi_action_players(text_input)

    for _ in range(duration):
        # Draw background (or fallback)
        if bg_image:
            screen.blit(bg_image, (0, 0))
        else:
            screen.fill((255, 255, 255))
            
        # Draw field lines over the background
        if grid == True:
            draw_field_lines_with_labels(screen, width, height, font)
            
        for p in players:
            p.update()
            p.draw(screen, font)
        pygame_img = pygame.surfarray.array3d(screen)
        pil_img = Image.fromarray(np.transpose(pygame_img, (1, 0, 2)))
        images.append(pil_img)

    images[0].save(output_path, save_all=True, append_images=images[1:], duration=33, loop=0)
    pygame.quit()
    return output_path

# --- Gradio Interface ---
def generate_multi_action_animation(user_input):
    gif_path = create_gif_multi_action(user_input, grid=False)
    return gif_path

# Sample input using trails
example_input = """
Group 1:
  Player 1:
    - at (100, 100)
    - trails true
    - move to (300, 200), wait 0, color (255, 0, 0), speed 8
    - move to (500, 400), wait 20, color (0, 255, 0), speed 4

Group 2:
  Player 2:
    - at (140, 100)
    - trails false
    - move to (300, 200), wait 5, color (0, 0, 255), speed 5
    - move to (450, 300), wait 10, color (255, 165, 0), speed 10
"""

interface = gr.Interface(
    fn=generate_multi_action_animation,
    inputs=gr.Textbox(label="Enter Player Actions", lines=20, value=example_input),
    outputs=gr.Image(type="filepath", label="Animated GIF"),
    title="Player Animator with Speed, Color, Trail, and Field Lines",
    description="Define player movements with optional speed, color, trails, and view field lines for reference."
)

interface.launch()

"""
to do s : 
    - add basic elements : 
        * arrows that show the player direction (single/multiple)
        * ball logics (passes, kicks, posession)
        * highlight the zones where players will move in
        * card system
        * time system (halfs, minutes, time for making decision)
        * legend
        * zooming on the field
        * replay/2x speed logic (automatic and button pause)
    - add advanced elements : 
        * strategic formations
        * display specific zones for each post (single/multiple ??)
    - integrate all the necessary elements for the animation system
    - give a rag to a llm
    - link the llm with the animation system
"""
