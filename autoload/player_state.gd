extends Node

signal changed

var player: Dictionary = {}
var _inventory_counts: Dictionary = {}

func load_from_dict(source: Dictionary) -> void:
	player = {
		"player_id": int(source.get("player_id", 10001)),
		"nickname": str(source.get("nickname", "巡厄弟子 %s" % int(source.get("player_id", 10001)))),
		"class_id": str(source.get("class_id", "")),
		"level": int(source.get("level", 1)),
		"exp": int(source.get("exp", 0)),
		"hp": int(source.get("hp", 850)),
		"max_hp": int(source.get("max_hp", 850)),
		"power": int(source.get("power", 0)),
		"gold": int(source.get("gold", 500)),
		"jade": int(source.get("jade", 0)),
		"contribution": int(source.get("contribution", 0)),
		"current_chapter_id": str(source.get("current_chapter_id", "")),
		"current_node_id": str(source.get("current_node_id", "")),
		"max_energy": int(source.get("max_energy", 100)),
		"class_profile": source.get("class_profile", _class_combat_profile(str(source.get("class_id", "")))).duplicate(true),
		"skill_points": int(source.get("skill_points", 0)),
		"skill_levels": _normalize_skill_levels(source.get("skill_levels", {})),
		"equipment_summary": source.get("equipment_summary", {}).duplicate(true),
		"build_summary": source.get("build_summary", {}).duplicate(true),
		"growth_recommendations": source.get("growth_recommendations", []).duplicate(true)
	}
	_inventory_counts.clear()
	for entry in source.get("inventory", []):
		add_item(str(entry.get("item_id", "")), int(entry.get("count", 0)), false)
	_prime_skill_levels(player.get("class_id", ""))
	if int(player.get("power", 0)) <= 0:
		player["power"] = int(get_total_stats().get("power", 0))
	emit_signal("changed")

func select_class(class_id: String) -> void:
	player["class_id"] = class_id
	player["class_profile"] = _class_combat_profile(class_id)
	_prime_skill_levels(class_id)
	restore_hp()
	emit_signal("changed")

func get_level() -> int:
	return int(player.get("level", 1))

func get_gold() -> int:
	return int(player.get("gold", 0))

func get_jade() -> int:
	return int(player.get("jade", 0))

func get_contribution() -> int:
	return int(player.get("contribution", 0))

func get_max_energy() -> int:
	return int(player.get("max_energy", 100))

func get_skill_points() -> int:
	return int(player.get("skill_points", 0))

func get_resource_name() -> String:
	match str(player.get("class_id", "")):
		"class_jingang":
			return "罡气"
		"class_lingyu":
			return "灵羽"
		"class_fulu":
			return "符炁"
		_:
			return "灵力"

func get_class_profile() -> Dictionary:
	return player.get("class_profile", {}).duplicate(true)

func get_player_name() -> String:
	return str(player.get("nickname", "巡厄弟子 %s" % str(player.get("player_id", 10001))))

func get_equipment_summary() -> Dictionary:
	return player.get("equipment_summary", {}).duplicate(true)

func get_build_summary() -> Dictionary:
	return player.get("build_summary", {}).duplicate(true)

func get_growth_recommendations() -> Array:
	return player.get("growth_recommendations", []).duplicate(true)

func get_equipped_item_ids() -> Array:
	return get_equipment_summary().get("equip_ids", []).duplicate(true)

func get_equipped_gem_ids() -> Array:
	return get_equipment_summary().get("equipped_gem_ids", []).duplicate(true)

func get_blue_affix_ids() -> Array:
	return get_equipment_summary().get("blue_affix_ids", []).duplicate(true)

func get_purple_refinement_ids() -> Array:
	return get_equipment_summary().get("purple_refinement_ids", []).duplicate(true)

func get_talisman_star_links() -> Array:
	return get_equipment_summary().get("talisman_star_links", []).duplicate(true)

func get_equipped_boss_core_ids() -> Array:
	return get_equipment_summary().get("equipped_boss_core_ids", []).duplicate(true)

func get_skill_level(skill_id: String) -> int:
	var skill_levels: Dictionary = player.get("skill_levels", {})
	return max(int(skill_levels.get(skill_id, 1)), 1)

func get_runtime_skills(type_filter: String = "") -> Array:
	var runtime_skills: Array = []
	for skill in GameData.get_skills_for_class(str(player.get("class_id", ""))):
		if int(skill.get("unlock_level", 1)) > get_level():
			continue
		if type_filter != "" and str(skill.get("type", "")) != type_filter:
			continue
		var entry: Dictionary = skill.duplicate(true)
		var level := get_skill_level(str(entry.get("skill_id", "")))
		entry["skill_level"] = level
		entry["scaled_power"] = int(entry.get("power_base", 0)) + max(level - 1, 0) * int(entry.get("power_per_level", 0))
		runtime_skills.append(entry)
	runtime_skills.sort_custom(func(a: Dictionary, b: Dictionary) -> bool:
		if int(a.get("unlock_level", 1)) != int(b.get("unlock_level", 1)):
			return int(a.get("unlock_level", 1)) < int(b.get("unlock_level", 1))
		return str(a.get("skill_id", "")) < str(b.get("skill_id", ""))
	)
	return runtime_skills

func can_upgrade_skill(skill_id: String) -> bool:
	var skill := GameData.get_skill(skill_id)
	if skill.is_empty():
		return false
	return get_skill_points() > 0 and get_skill_level(skill_id) < int(skill.get("max_level", 1))

func upgrade_skill(skill_id: String) -> bool:
	if not can_upgrade_skill(skill_id):
		return false
	var skill_levels: Dictionary = player.get("skill_levels", {})
	skill_levels[skill_id] = get_skill_level(skill_id) + 1
	player["skill_levels"] = skill_levels
	player["skill_points"] = max(get_skill_points() - 1, 0)
	restore_hp()
	emit_signal("changed")
	return true

func get_total_stats() -> Dictionary:
	var base_atk := 28 + (get_level() - 1) * 4
	var base_def := 18 + (get_level() - 1) * 3
	var bonus_hp := 0
	var bonus_boss_dmg := 0
	var bonus_attack_speed := 0.0
	var bonus_damage_ratio := 0.0
	var class_bonuses := _class_stat_bonuses(str(player.get("class_id", "")))
	base_atk += int(class_bonuses.get("bonus_atk", 0))
	base_def += int(class_bonuses.get("bonus_def", 0))
	bonus_hp += int(class_bonuses.get("bonus_hp", 0))
	bonus_boss_dmg += int(class_bonuses.get("bonus_boss_dmg", 0))
	bonus_attack_speed += float(class_bonuses.get("bonus_attack_speed", 0.0))
	bonus_damage_ratio += float(class_bonuses.get("bonus_damage_ratio", 0.0))

	for equip_id in get_equipped_item_ids():
		var equipment_data := GameData.get_equipment(str(equip_id))
		base_atk += int(equipment_data.get("base_atk", 0))
		base_def += int(equipment_data.get("base_def", 0))

	for gem_id in get_equipped_gem_ids():
		var gem_data := GameData.get_gem(str(gem_id))
		base_atk += int(gem_data.get("bonus_atk", 0))
		bonus_boss_dmg += int(gem_data.get("bonus_boss_dmg", 0))

	for affix_id in get_blue_affix_ids():
		var affix_data := GameData.get_blue_affix(str(affix_id))
		var bonuses: Dictionary = affix_data.get("bonuses", {})
		base_atk += int(bonuses.get("bonus_atk", 0))
		base_def += int(bonuses.get("bonus_def", 0))
		bonus_hp += int(bonuses.get("bonus_hp", 0))
		bonus_attack_speed += float(bonuses.get("bonus_attack_speed", 0.0))
		bonus_damage_ratio += float(bonuses.get("bonus_damage_ratio", 0.0))

	for refinement_id in get_purple_refinement_ids():
		var refinement_data := GameData.get_purple_refinement(str(refinement_id))
		var refinement_bonuses: Dictionary = refinement_data.get("bonuses", {})
		base_atk += int(refinement_bonuses.get("bonus_atk", 0))
		base_def += int(refinement_bonuses.get("bonus_def", 0))
		bonus_hp += int(refinement_bonuses.get("bonus_hp", 0))
		bonus_boss_dmg += int(refinement_bonuses.get("bonus_boss_dmg", 0))
		bonus_attack_speed += float(refinement_bonuses.get("bonus_attack_speed", 0.0))
		bonus_damage_ratio += float(refinement_bonuses.get("bonus_damage_ratio", 0.0))

	for set_count in get_equipment_summary().get("set_counts", []):
		var set_data := GameData.get_set(str(set_count.get("set_id", "")))
		for effect in set_data.get("effects", []):
			if int(set_count.get("equipped_count", 0)) < int(effect.get("count", 0)):
				continue
			base_atk += int(effect.get("bonus_atk", 0))
			base_def += int(effect.get("bonus_def", 0))
			bonus_hp += int(effect.get("bonus_hp", 0))
			bonus_boss_dmg += int(effect.get("bonus_boss_dmg", 0))
			bonus_attack_speed += float(effect.get("bonus_attack_speed", 0.0))
			bonus_damage_ratio += float(effect.get("bonus_damage_ratio", 0.0))

	for skill in get_runtime_skills("passive"):
		var skill_level := int(skill.get("skill_level", 1))
		var bonuses: Dictionary = skill.get("stat_bonuses", {})
		base_atk += int(bonuses.get("bonus_atk", 0)) * skill_level
		base_def += int(bonuses.get("bonus_def", 0)) * skill_level
		bonus_hp += int(bonuses.get("bonus_hp", 0)) * skill_level
		bonus_boss_dmg += int(bonuses.get("bonus_boss_dmg", 0)) * skill_level
		bonus_attack_speed += float(bonuses.get("bonus_attack_speed", 0.0)) * skill_level
		bonus_damage_ratio += float(bonuses.get("bonus_damage_ratio", 0.0)) * skill_level

	var max_hp := int(player.get("max_hp", 850)) + bonus_hp
	return {
		"atk": base_atk,
		"def": base_def,
		"max_hp": max_hp,
		"boss_dmg": bonus_boss_dmg,
		"attack_speed_bonus": bonus_attack_speed,
		"damage_ratio_bonus": bonus_damage_ratio,
		"power": int(base_atk * 2.2 + base_def * 1.8 + max_hp * 0.2 + bonus_boss_dmg * 3.0 + bonus_attack_speed * 80.0 + bonus_damage_ratio * 120.0)
	}

func get_power() -> int:
	var runtime_power := int(player.get("power", 0))
	if runtime_power > 0:
		return runtime_power
	return int(get_total_stats().get("power", 0))

func add_item(item_id: String, count: int = 1, notify: bool = true) -> void:
	if item_id.is_empty() or count <= 0:
		return
	_inventory_counts[item_id] = int(_inventory_counts.get(item_id, 0)) + count
	if notify:
		emit_signal("changed")

func apply_rewards(rewards: Array) -> void:
	for reward in rewards:
		var item_id := str(reward.get("item_id", ""))
		var count := int(reward.get("count", 0))
		match item_id:
			"gold":
				player["gold"] = int(player.get("gold", 0)) + count
			"jade":
				player["jade"] = int(player.get("jade", 0)) + count
			"contribution":
				player["contribution"] = int(player.get("contribution", 0)) + count
			"skill_point":
				player["skill_points"] = get_skill_points() + count
			_:
				add_item(item_id, count, false)
	restore_hp()
	emit_signal("changed")

func restore_hp() -> void:
	var stats := get_total_stats()
	player["hp"] = int(stats.get("max_hp", player.get("max_hp", 850)))
	player["power"] = int(stats.get("power", player.get("power", 0)))

func get_inventory_entries() -> Array:
	var entries: Array = []
	for item_id in _inventory_counts.keys():
		entries.append({
			"item_id": item_id,
			"count": int(_inventory_counts[item_id]),
			"definition": GameData.get_item_definition(str(item_id))
		})
	entries.sort_custom(func(a: Dictionary, b: Dictionary) -> bool:
		return str(a.get("item_id", "")) < str(b.get("item_id", ""))
	)
	return entries

func get_item_count(item_id: String) -> int:
	return int(_inventory_counts.get(item_id, 0))

func is_feature_unlocked(feature: Dictionary) -> bool:
	var unlock_condition: Dictionary = feature.get("unlock_condition", {})
	return get_level() >= int(unlock_condition.get("level", 1))

func _normalize_skill_levels(source: Variant) -> Dictionary:
	if source is Dictionary:
		return source.duplicate(true)
	return {}

func _prime_skill_levels(class_id: String) -> void:
	var skill_levels: Dictionary = player.get("skill_levels", {})
	for skill in GameData.get_skills_for_class(class_id, true):
		var skill_id := str(skill.get("skill_id", ""))
		if skill_id.is_empty() or skill_levels.has(skill_id):
			continue
		skill_levels[skill_id] = 1
	player["skill_levels"] = skill_levels

func _class_stat_bonuses(class_id: String) -> Dictionary:
	match class_id:
		"class_jingang":
			return {
				"bonus_atk": 4,
				"bonus_def": 12,
				"bonus_hp": 160,
				"bonus_damage_ratio": 0.04
			}
		"class_lingyu":
			return {
				"bonus_atk": 14,
				"bonus_def": -3,
				"bonus_hp": -70,
				"bonus_attack_speed": 0.12,
				"bonus_damage_ratio": 0.08
			}
		"class_fulu":
			return {
				"bonus_atk": 10,
				"bonus_def": -1,
				"bonus_hp": 40,
				"bonus_boss_dmg": 6,
				"bonus_damage_ratio": 0.12
			}
		_:
			return {}

func _class_combat_profile(class_id: String) -> Dictionary:
	match class_id:
		"class_jingang":
			return {
				"role": "melee_tank",
				"preferred_range": 78,
				"move_speed": 186,
				"attack_range": 88,
				"attack_interval": 1.05,
				"resource_regen": 11,
				"target_priority": "nearest",
				"kite_distance": 0
			}
		"class_lingyu":
			return {
				"role": "ranged_dps",
				"preferred_range": 164,
				"move_speed": 208,
				"attack_range": 172,
				"attack_interval": 0.82,
				"resource_regen": 14,
				"target_priority": "farthest_cluster",
				"kite_distance": 108
			}
		"class_fulu":
			return {
				"role": "caster_control",
				"preferred_range": 150,
				"move_speed": 194,
				"attack_range": 156,
				"attack_interval": 0.92,
				"resource_regen": 13,
				"target_priority": "boss_or_high_threat",
				"kite_distance": 84
			}
		_:
			return {
				"role": "adventurer",
				"preferred_range": 100,
				"move_speed": 190,
				"attack_range": 84,
				"attack_interval": 1.0,
				"resource_regen": 12,
				"target_priority": "nearest",
				"kite_distance": 0
			}
