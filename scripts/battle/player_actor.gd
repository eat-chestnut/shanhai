extends "res://scripts/battle/combat_actor.gd"
class_name PlayerActor

signal skill_state_changed(skill_states: Array, current_resource: float, max_resource: float, resource_name: String)

var enemies: Array = []
var class_id := ""
var class_profile: Dictionary = {}
var resource_name := "灵力"
var resource_max := 100.0
var resource_current := 100.0
var active_skills: Array = []
var passive_skills: Array = []

var _skill_cooldowns: Dictionary = {}
var _rng := RandomNumberGenerator.new()

func _ready() -> void:
	super._ready()
	_rng.randomize()

func setup_actor(config: Dictionary) -> void:
	super.setup_actor(config)
	class_id = str(config.get("class_id", ""))
	class_profile = config.get("class_profile", {}).duplicate(true)
	resource_name = str(config.get("resource_name", resource_name))
	resource_max = float(config.get("resource_max", resource_max))
	resource_current = resource_max
	active_skills = config.get("active_skills", []).duplicate(true)
	passive_skills = config.get("passive_skills", []).duplicate(true)
	move_speed = float(class_profile.get("move_speed", config.get("move_speed", move_speed)))
	attack_range = float(class_profile.get("attack_range", config.get("attack_range", attack_range)))
	attack_interval = float(class_profile.get("attack_interval", config.get("attack_interval", attack_interval)))
	_skill_cooldowns.clear()
	for skill in active_skills:
		_skill_cooldowns[str(skill.get("skill_id", ""))] = 0.0
	var attack_speed_bonus := float(config.get("attack_speed_bonus", 0.0))
	if attack_speed_bonus > 0.0:
		attack_interval = max(0.45, attack_interval * max(0.45, 1.0 - attack_speed_bonus))
	_emit_skill_state()

func _physics_process(delta: float) -> void:
	tick_actor(delta)
	if not is_alive():
		velocity = Vector2.ZERO
		move_and_slide()
		return

	_tick_skills(delta)
	var target = _nearest_enemy()
	var input_vector := Input.get_vector("ui_left", "ui_right", "ui_up", "ui_down")
	if can_move():
		if input_vector.length() > 0.0:
			velocity = input_vector.normalized() * move_speed
		else:
			velocity = _auto_position_velocity(target)
	else:
		velocity = Vector2.ZERO

	move_and_slide()
	clamp_to_arena()

	if target == null:
		_emit_skill_state()
		return

	if _try_cast_skill(target):
		_emit_skill_state()
		return

	if can_attack() and global_position.distance_to(target.global_position) <= attack_range:
		var dealt := attack_target(target)
		if dealt > 0:
			_apply_passive_triggers("on_hit", target)

	_emit_skill_state()

func _tick_skills(delta: float) -> void:
	resource_current = min(resource_current + delta * float(class_profile.get("resource_regen", 12.0)), resource_max)
	for skill_id in _skill_cooldowns.keys():
		_skill_cooldowns[skill_id] = max(float(_skill_cooldowns.get(skill_id, 0.0)) - delta, 0.0)

func _try_cast_skill(primary_target) -> bool:
	for skill in active_skills:
		var skill_id := str(skill.get("skill_id", ""))
		if skill_id.is_empty():
			continue
		if float(_skill_cooldowns.get(skill_id, 0.0)) > 0.0:
			continue
		if resource_current < float(skill.get("cost", 0)):
			continue

		var targets := _resolve_skill_targets(skill, primary_target)
		var target_type := str(skill.get("target_type", "single"))
		if target_type != "self" and targets.is_empty():
			continue

		var distance_limit := attack_range * (1.7 if target_type == "multi" or target_type == "area" else 1.35)
		if target_type != "self" and global_position.distance_to(primary_target.global_position) > distance_limit:
			continue

		_cast_skill(skill, targets)
		return true
	return false

func _cast_skill(skill: Dictionary, targets: Array) -> void:
	var skill_id := str(skill.get("skill_id", ""))
	var skill_name := str(skill.get("skill_name", skill_id))
	var effect_type := str(skill.get("effect_type", "damage"))
	var telegraph_type := str(skill.get("effect_payload", {}).get("telegraph_type", ""))
	resource_current = max(resource_current - float(skill.get("cost", 0)), 0.0)
	_skill_cooldowns[skill_id] = float(skill.get("cooldown", 0))
	_attack_cooldown = max(_attack_cooldown, 0.35)
	emit_signal("combat_event", "%s 施放 %s" % [display_name, skill_name])
	if telegraph_type != "":
		emit_signal("combat_event", "技能提示：%s 以%s方式展开。" % [skill_name, telegraph_type])

	match effect_type:
		"damage":
			for target in targets:
				var dealt: int = target.receive_damage(_skill_damage(skill), self)
				emit_signal("attacked", self, target, dealt)
				_apply_status_payload(target, skill)
				_apply_self_heal(skill, dealt)
				_apply_passive_triggers("on_skill_hit", target)
		"dot":
			for target in targets:
				target.add_status(_build_status_from_skill(skill))
		"hot":
			for target in targets:
				target.add_status(_build_status_from_skill(skill))
		"control":
			for target in targets:
				var dealt: int = 0
				if int(skill.get("scaled_power", 0)) > 0:
					dealt = target.receive_damage(_skill_damage(skill), self)
					emit_signal("attacked", self, target, dealt)
				target.add_status(_build_status_from_skill(skill))
				_apply_self_heal(skill, dealt)
		_:
			for target in targets:
				target.add_status(_build_status_from_skill(skill))

func _resolve_skill_targets(skill: Dictionary, primary_target) -> Array:
	var target_type := str(skill.get("target_type", "single"))
	if target_type == "self":
		return [self]
	if primary_target == null or not primary_target.is_alive():
		return []
	if target_type == "single":
		var priority_target = _preferred_enemy(str(skill.get("effect_payload", {}).get("preferred_target", class_profile.get("target_priority", "nearest"))))
		return [priority_target if priority_target != null else primary_target]

	var payload: Dictionary = skill.get("effect_payload", {})
	var target_count: int = max(int(payload.get("target_count", 3)), 1)
	var candidates: Array = []
	for candidate in enemies:
		if candidate == null or not candidate.is_alive():
			continue
		if global_position.distance_to(candidate.global_position) > attack_range * 1.9:
			continue
		candidates.append(candidate)

	candidates.sort_custom(func(a, b) -> bool:
		var priority := str(payload.get("preferred_target", class_profile.get("target_priority", "nearest")))
		if priority == "farthest_cluster":
			return global_position.distance_to(a.global_position) > global_position.distance_to(b.global_position)
		if priority == "boss_or_high_threat":
			if bool(a.is_boss) != bool(b.is_boss):
				return bool(a.is_boss)
			return float(a.attack) > float(b.attack)
		return global_position.distance_to(a.global_position) < global_position.distance_to(b.global_position)
	)
	return candidates.slice(0, min(target_count, candidates.size()))

func _skill_damage(skill: Dictionary) -> float:
	var ratio: float = max(float(skill.get("scaled_power", 0)) / 100.0, 0.1)
	return attack * ratio

func _apply_status_payload(target, skill: Dictionary) -> void:
	var payload: Dictionary = skill.get("effect_payload", {})
	if not payload.has("status_type"):
		return
	target.add_status(_build_status_from_skill(skill))

func _apply_self_heal(skill: Dictionary, dealt: int) -> void:
	var payload: Dictionary = skill.get("effect_payload", {})
	var self_heal_ratio := float(payload.get("self_heal_ratio", 0.0))
	if self_heal_ratio <= 0.0 or dealt <= 0:
		return
	var healed := heal(float(dealt) * self_heal_ratio)
	emit_signal("combat_event", "%s 因技能回复 %d 生命" % [display_name, healed])

func _build_status_from_skill(skill: Dictionary) -> Dictionary:
	var payload: Dictionary = skill.get("effect_payload", {})
	var status_type := str(payload.get("status_type", skill.get("effect_type", "")))
	var status_duration := float(payload.get("status_duration", skill.get("duration", 0)))
	var status_tick_interval := float(payload.get("status_tick_interval", 1.0))
	var status_power_ratio := float(payload.get("status_power_ratio", max(float(skill.get("scaled_power", 0)) / 100.0 * 0.2, 0.12)))

	return {
		"status_id": str(payload.get("status_id", skill.get("skill_id", ""))),
		"name": str(payload.get("status_name", skill.get("skill_name", "状态"))),
		"type": status_type,
		"duration": status_duration,
		"tick_interval": status_tick_interval,
		"power": max(6.0, attack * status_power_ratio),
		"stack_rule": str(payload.get("stack_rule", "refresh")),
		"max_stacks": int(payload.get("max_stacks", 1)),
		"move_locked": _as_bool(payload.get("move_locked", status_type == "control")),
		"attack_locked": _as_bool(payload.get("attack_locked", status_type == "control"))
	}

func _apply_passive_triggers(trigger_name: String, target) -> void:
	for skill in passive_skills:
		if str(skill.get("effect_type", "")) != "trigger":
			continue
		var payload: Dictionary = skill.get("effect_payload", {})
		if str(payload.get("trigger", "")) != trigger_name:
			continue
		if _rng.randf() > float(skill.get("chance", 0.0)):
			continue

		emit_signal("combat_event", "%s 的被动[%s]触发。" % [display_name, skill.get("skill_name", skill.get("skill_id", "被动"))])

		var extra_damage_ratio := float(payload.get("extra_damage_ratio", 0.0))
		if target != null and target.is_alive() and extra_damage_ratio > 0.0:
			var dealt: int = target.receive_damage(max(1.0, attack * extra_damage_ratio), self)
			emit_signal("attacked", self, target, dealt)

		if target != null and target.is_alive() and payload.has("status_type"):
			target.add_status(_build_status_from_skill(skill))

		var self_heal_ratio := float(payload.get("self_heal_ratio", 0.0))
		if self_heal_ratio > 0.0:
			var healed := heal(max(4.0, attack * self_heal_ratio))
			emit_signal("combat_event", "%s 因被动回复 %d 生命。" % [display_name, healed])

func _emit_skill_state() -> void:
	var states: Array = []
	for skill in active_skills:
		var skill_id := str(skill.get("skill_id", ""))
		states.append({
			"skill_id": skill_id,
			"skill_name": str(skill.get("skill_name", skill_id)),
			"cooldown_left": snapped(float(_skill_cooldowns.get(skill_id, 0.0)), 0.1),
			"cost": int(skill.get("cost", 0)),
			"type": str(skill.get("effect_type", "")),
			"level": int(skill.get("skill_level", 1))
		})
	emit_signal("skill_state_changed", states, resource_current, resource_max, resource_name)

func _nearest_enemy():
	return _preferred_enemy(str(class_profile.get("target_priority", "nearest")))

func _preferred_enemy(priority: String):
	var nearest = null
	var nearest_distance: float = INF
	var farthest_distance: float = -INF
	var best_attack := -INF
	for candidate in enemies:
		if candidate == null or not candidate.is_alive():
			continue
		var distance := global_position.distance_to(candidate.global_position)
		match priority:
			"farthest_cluster":
				if distance > farthest_distance:
					farthest_distance = distance
					nearest = candidate
			"boss_or_high_threat":
				if candidate.is_boss and (nearest == null or not nearest.is_boss):
					nearest = candidate
					best_attack = float(candidate.attack)
					continue
				if nearest == null or (not nearest.is_boss and float(candidate.attack) > best_attack):
					nearest = candidate
					best_attack = float(candidate.attack)
			_:
				if distance < nearest_distance:
					nearest_distance = distance
					nearest = candidate
	return nearest

func _auto_position_velocity(target) -> Vector2:
	if target == null or not target.is_alive():
		return Vector2.ZERO
	var desired_range := float(class_profile.get("preferred_range", attack_range))
	var kite_distance := float(class_profile.get("kite_distance", 0.0))
	var direction: Vector2 = target.global_position - global_position
	var distance: float = direction.length()
	if kite_distance > 0.0 and distance < kite_distance:
		return -direction.normalized() * move_speed * 0.62
	if distance > desired_range * 1.05:
		return direction.normalized() * move_speed * 0.55
	return Vector2.ZERO

func _as_bool(value: Variant) -> bool:
	if value is bool:
		return value
	if value is int:
		return value != 0
	if value is float:
		return value != 0.0
	var normalized := str(value).to_lower()
	return normalized == "1" or normalized == "true" or normalized == "yes"
