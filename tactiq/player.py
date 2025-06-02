import pygame

class Player:
    def __init__(self, id, position, color=(0, 0, 255), speed=5, radius=15, label=None):
        self.id = id
        self.x, self.y = position
        self.color = color
        self.speed = speed
        self.radius = radius
        self.label = label
        self.dx = 0
        self.dy = 0
        self.target = None
        self.wait_frames = 0

    def set_target(self, target_pos):
        self.target = target_pos
        if target_pos:
            self.dx = (target_pos[0] - self.x) / 30
            self.dy = (target_pos[1] - self.y) / 30

    def update(self):
        if self.wait_frames > 0:
            self.wait_frames -= 1
            return
        if self.target:
            self.x += self.dx
            self.y += self.dy

    def draw(self, screen, font=None):
        pygame.draw.circle(screen, self.color, (int(self.x), int(self.y)), self.radius)
        if self.label and font:
            text = font.render(str(self.label), True, (0, 0, 0))
            screen.blit(text, (self.x - 5, self.y - 5))
