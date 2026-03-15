extends "res://scripts/battle/combat_actor.gd"
class_name EnemyActor

var player_actor
var _aggro := false
var _phase := 0.0
var _skill_cooldown := 0.0

func _ready() -> void:
	super._ready()
	_phase = randf() * TAU

func _physics_process(delta: float) -> void:
	tick_actor(delta)
	if not is_alive():
		velocity = Vector2.ZERO
		move_and_slide()
		return

	_skill_cooldown = max(_skill_cooldown - delta, 0.0)

	if player_actor == null or not player_actor.is_alive():
		_patrol()
		move_and_slide()
		clamp_to_arena()
		return

	var distance_to_player := global_position.distance_to(player_actor.global_position)
	if distance_to_player <= aggro_range:
		_aggro = true

	if _aggro and (global_position.distance_to(home_position) > aggro_range * 2.3 or distance_to_player > aggro_range * 1.7):
		_aggro = false

	if _aggro:
		if can_move() and distance_to_player > attack_range * 0.9:
			velocity = (player_actor.global_position - global_position).normalized() * move_speed
		else:
			velocity = Vector2.ZERO

		if distance_to_player <= attack_range and can_attack():
			var dealt := attack_target(player_actor)
			if dealt > 0 and _skill_cooldown <= 0.0:
				if is_boss:
					player_actor.add_status({
						"name": "震慑",
						"type": "control",
						"duration": 1.2,
						"move_locked": true,
						"attack_locked": true
					})
					player_actor.add_status({
						"name": "妖火",
						"type": "dot",
						"duration": 4.0,
						"tick_interval": 1.0,
						"power": max(10.0, attack * 0.2)
					})
					_skill_cooldown = 6.0
				else:
					player_actor.add_status({
						"name": "侵蚀",
						"type": "dot",
						"duration": 3.0,
						"tick_interval": 1.0,
						"power": max(6.0, attack * 0.16)
					})
					_skill_cooldown = 4.0
	else:
		_patrol()

	move_and_slide()
	clamp_to_arena()

func _patrol() -> void:
	if not can_move():
		velocity = Vector2.ZERO
		return
	var time := Time.get_ticks_msec() / 1000.0
	var patrol_target := home_position + Vector2(cos(time + _phase), sin(time * 0.9 + _phase)) * 28.0
	var direction := patrol_target - global_position
	if direction.length() > 8.0:
		velocity = direction.normalized() * move_speed * 0.4
	else:
		velocity = Vector2.ZERO
