# =========================
# IMPORTS & UTILITIES
# =========================

import re
from collections import defaultdict
import random
import math


# =========================
# UTILS
# =========================

# -------------------------
# Helper: Check if position is too close
# -------------------------
def is_too_close(x, y, existing_positions, min_distance=75):
    for (ex, ey) in existing_positions:
        if math.hypot(x - ex, y - ey) < min_distance:
            return True
    return False


# -------------------------
# Phase splitter
# -------------------------
def split_into_phases(dsl_text):
    lines = dsl_text.strip().splitlines()
    phases = []
    current_phase_name = "Global"
    current_content = []

    for line in lines:
        stripped = line.strip()
        phase_match = re.match(r"Phase\s+\d+[:\-]?", stripped, re.IGNORECASE)
        
        if phase_match:
            # Save previous phase before switching
            if current_content:
                phases.append((current_phase_name, "\n".join(current_content)))
                current_content = []
            current_phase_name = phase_match.group(0).strip()
        else:
            current_content.append(stripped)

    if current_content:
        phases.append((current_phase_name, "\n".join(current_content)))

    return phases

# =========================
# STEP 1: Insert Missing Players
# =========================

def instancier_joueurs_references_en_haut(dsl_text):
    lignes = dsl_text.strip().splitlines()
    definis = set()
    references = set()
    existing_positions = []

    current_player = None
    for ligne in lignes:
        match_player = re.match(r"Player\s+([\w\-]+):", ligne.strip())
        if match_player:
            current_player = match_player.group(1).strip()
            definis.add(current_player)
        else:
            match_at = re.match(r"-\s*at\s*\((\d+),\s*(\d+)\)", ligne.strip())
            if match_at and current_player:
                x, y = int(match_at.group(1)), int(match_at.group(2))
                existing_positions.append((x, y))

    for ligne in lignes:
        refs = re.findall(r"Player\s+([\w\-]+)", ligne)
        references.update([r.strip() for r in refs])

    manquants = list(references - definis)
    blocs_a_inserer = []

    for joueur in sorted(manquants):
        for _ in range(100):
            x, y = random.randint(50, 600), random.randint(50, 400)
            if not is_too_close(x, y, existing_positions):
                existing_positions.append((x, y))
                blocs_a_inserer.append(f"Player {joueur}:\n - at ({x}, {y})")
                break

    if blocs_a_inserer:
        return "\n".join(blocs_a_inserer) + "\n" + dsl_text.strip()
    return dsl_text

# =========================
# STEP 2: Add Move Logic for Fouls
# =========================

def handle_foul_movements(dsl_text):
    lines = dsl_text.strip().splitlines()
    current_player = None
    player_positions = {}
    player_destinations = {}
    player_blocks = defaultdict(list)

    for line in lines:
        line = line.strip()
        player_match = re.match(r"Player (\w+):", line)
        if player_match:
            current_player = player_match.group(1)
            player_blocks[current_player].append(line)
        elif current_player:
            player_blocks[current_player].append(line)
            at_match = re.match(r"-\s*at\s*\((\d+),\s*(\d+)\)", line)
            if at_match:
                x, y = int(at_match.group(1)), int(at_match.group(2))
                player_positions[current_player] = (x, y)
            move_match = re.match(r"-\s*move\s+to\s+\((\d+),\s*(\d+)\)", line)
            if move_match:
                dx, dy = int(move_match.group(1)), int(move_match.group(2))
                player_destinations[current_player] = (dx, dy)

    for player, block in player_blocks.items():
        modified_block = []
        for line in block:
            foul_match = re.match(r"-\s*foul(?:\s+on)?\s+Player\s+(\w+)", line)
            if foul_match:
                target = foul_match.group(1)
                if player == target:
                    modified_block.append(line)
                    continue
                if target in player_destinations:
                    target_pos = player_destinations[target]
                else:
                    target_pos = player_positions.get(target, (0, 0))
                move_line = f"- move to ({target_pos[0]}, {target_pos[1]})"
                if not any("move to" in l and str(target_pos[0]) in l and str(target_pos[1]) in l for l in modified_block):
                    modified_block.append(move_line)
            modified_block.append(line)
        player_blocks[player] = modified_block

    return "\n".join("\n".join(block) for block in player_blocks.values())

# =========================
# STEP 3: Assign Waits and Ball Possession
# =========================

def assign_waits_and_sequence(dsl_text):
    players = defaultdict(list)
    current_player = None

    for line in dsl_text.strip().splitlines():
        line = line.strip()
        player_match = re.match(r"Player (\w+):", line)
        if player_match:
            current_player = player_match.group(1)
            players[current_player].append("HEADER")
        elif current_player:
            players[current_player].append(line)

    wait_tracker = defaultdict(int)
    pass_events = {}

    for player, actions in players.items():
        updated = []
        current_wait = wait_tracker[player]

        for action in actions:
            if action == "HEADER":
                updated.append(f"Player {player}:")
                continue

            already_has_wait = re.search(r"\bwait\s+\d+", action)

            if "pass to Player" in action:
                match = re.search(r"pass to Player (\w+)", action)
                if match:
                    receiver = match.group(1)
                    pass_time = current_wait + 2
                    pass_events[receiver] = pass_time
                    action = action.lstrip("- ").strip()
                    updated.append(f"- {action}" if already_has_wait else f"- {action}, wait {current_wait}")
                    current_wait += 2
                    continue

            if "kick ball" in action or 'move to' in action:
                action = action.lstrip("- ").strip()
                updated.append(f"- {action}" if already_has_wait else f"- {action}, wait {current_wait}")
                current_wait += 2
                continue

            if "possess ball" in action:
                updated.append(f"- {action}" if already_has_wait else f"- possess ball, wait {current_wait}")
                current_wait += 1
                continue

            updated.append("- " + action.lstrip("- ").strip())

        wait_tracker[player] = current_wait
        players[player] = updated

    for player, t in pass_events.items():
        if player in players:
            if not any("possess ball" in line for line in players[player]):
                players[player].insert(1, f"- possess ball, wait {t}")
        else:
            players[player] = [f"Player {player}:", f"- at (0, 0)", f"- possess ball, wait {t}"]

    return "\n".join(
        "\n".join(line for line in block if line.strip() and line.strip() != "-")
        for block in players.values()
    )

# =========================
# STEP 4: Clean Formatting
# =========================

def clean_formatting(dsl_text):
    cleaned_lines = []
    for line in dsl_text.strip().splitlines():
        line = line.strip()
        line = re.sub(r"^-+\s*", "- ", line)
        line = re.sub(r"\s+wait", ", wait", line)
        line = re.sub(r",+", ",", line)
        cleaned_lines.append(line)
    return "\n".join(cleaned_lines)

# =========================
# STEP 5: Moving reset times at the bottom
# =========================

def move_resetatframe_to_bottom(dsl_text):
    lines = dsl_text.strip().splitlines()
    kept_lines = []
    reset_lines = []

    for line in lines:
        if 'resetatframe' in line:
            parts = ['-', 'resetatframe', '150']
            result = " ".join(parts[-2:])            
            reset_lines.append(result)
        else:
            kept_lines.append(line)

    return "\n".join(kept_lines + reset_lines)




# =========================
# MASTER PIPELINE
# =========================

def process_dsl_pipeline(dsl_text):
    phases = split_into_phases(dsl_text)
    processed = []
    for phase_name, content in phases:
        step1 = instancier_joueurs_references_en_haut(content)
        step2 = handle_foul_movements(step1)
        step3 = assign_waits_and_sequence(step2)
        step4 = clean_formatting(step3)
        step5 = move_resetatframe_to_bottom(step4)
        processed.append(f"{phase_name}:\n{step5}")
    return "\n\n".join(processed)


# ================== TESTING ================== #

dsl_test = """
Player Mick:
- at (200, 200)
- pass to Player Jane

Player John:
- at (200, 200)
- pass to Player Bog
"""

print(process_dsl_pipeline(dsl_test))