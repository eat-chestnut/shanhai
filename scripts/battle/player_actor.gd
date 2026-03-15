extends "res://scripts/battle/combat_actor.gd"
class_name PlayerActor

var enemies: Array = []
var _combo_hits := 0
var _regen_cooldown := 0.0

func _physics_process(delta: float) -> void:
	tick_actor(delta)
	if not is_alive():
		velocity = Vector2.ZERO
		move_and_slide()
		return

	_regen_cooldown = max(_regen_cooldown - delta, 0.0)
	var input_vector := Input.get_vector("ui_left", "ui_right", "ui_up", "ui_down")
	if can_move():
		velocity = input_vector.normalized() * move_speed
	else:
		velocity = Vector2.ZERO

	move_and_slide()
	clamp_to_arena()

	var target = _nearest_enemy()
	if target != null and can_attack() and global_position.distance_to(target.global_position) <= attack_range:
		var dealt := attack_target(target)
		if dealt > 0:
			_combo_hits += 1
			if _combo_hits % 3 == 0:
				target.add_status({
					"name": "灼烧",
					"type": "dot",
					"duration": 4.0,
					"tick_interval": 1.0,
					"power": max(6.0, attack * 0.18)
				})

	if _regen_cooldown <= 0.0 and hp < max_hp * 0.88:
		add_status({
			"name": "回春",
			"type": "hot",
			"duration": 4.0,
			"tick_interval": 1.0,
			"power": max(8.0, attack * 0.15)
		})
		_regen_cooldown = 8.0

func _nearest_enemy():
	var nearest = null
	var nearest_distance: float = INF
	for candidate in enemies:
		if candidate == null or not candidate.is_alive():
			continue
		var distance := global_position.distance_to(candidate.global_position)
		if distance < nearest_distance:
			nearest_distance = distance
			nearest = candidate
	return nearest
