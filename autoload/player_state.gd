extends Node

signal changed

var player: Dictionary = {}
var _inventory_counts: Dictionary = {}

func load_from_dict(source: Dictionary) -> void:
	player = {
		"player_id": int(source.get("player_id", 10001)),
		"class_id": str(source.get("class_id", "")),
		"level": int(source.get("level", 1)),
		"exp": int(source.get("exp", 0)),
		"hp": int(source.get("hp", 850)),
		"max_hp": int(source.get("max_hp", 850)),
		"gold": int(source.get("gold", 500)),
		"equipment_summary": source.get("equipment_summary", {}).duplicate(true)
	}
	_inventory_counts.clear()
	for entry in source.get("inventory", []):
		add_item(str(entry.get("item_id", "")), int(entry.get("count", 0)), false)
	emit_signal("changed")

func select_class(class_id: String) -> void:
	player["class_id"] = class_id
	emit_signal("changed")

func get_level() -> int:
	return int(player.get("level", 1))

func get_gold() -> int:
	return int(player.get("gold", 0))

func get_player_name() -> String:
	return "巡厄弟子 %s" % str(player.get("player_id", 10001))

func get_equipment_summary() -> Dictionary:
	return player.get("equipment_summary", {}).duplicate(true)

func get_equipped_item_ids() -> Array:
	return get_equipment_summary().get("equip_ids", []).duplicate(true)

func get_equipped_gem_ids() -> Array:
	return get_equipment_summary().get("equipped_gem_ids", []).duplicate(true)

func get_blue_affix_ids() -> Array:
	return get_equipment_summary().get("blue_affix_ids", []).duplicate(true)

func get_purple_refinement_ids() -> Array:
	return get_equipment_summary().get("purple_refinement_ids", []).duplicate(true)

func get_total_stats() -> Dictionary:
	var base_atk := 28 + (get_level() - 1) * 4
	var base_def := 18 + (get_level() - 1) * 3
	var bonus_hp := 0
	var bonus_boss_dmg := 0

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

	for refinement_id in get_purple_refinement_ids():
		var refinement_data := GameData.get_purple_refinement(str(refinement_id))
		var refinement_bonuses: Dictionary = refinement_data.get("bonuses", {})
		base_atk += int(refinement_bonuses.get("bonus_atk", 0))
		base_def += int(refinement_bonuses.get("bonus_def", 0))
		bonus_hp += int(refinement_bonuses.get("bonus_hp", 0))
		bonus_boss_dmg += int(refinement_bonuses.get("bonus_boss_dmg", 0))

	for set_count in get_equipment_summary().get("set_counts", []):
		var set_data := GameData.get_set(str(set_count.get("set_id", "")))
		for effect in set_data.get("effects", []):
			if int(set_count.get("equipped_count", 0)) < int(effect.get("count", 0)):
				continue
			base_atk += int(effect.get("bonus_atk", 0))
			base_def += int(effect.get("bonus_def", 0))
			bonus_hp += int(effect.get("bonus_hp", 0))
			bonus_boss_dmg += int(effect.get("bonus_boss_dmg", 0))

	var max_hp := int(player.get("max_hp", 850)) + bonus_hp
	return {
		"atk": base_atk,
		"def": base_def,
		"max_hp": max_hp,
		"boss_dmg": bonus_boss_dmg,
		"power": int(base_atk * 2.2 + base_def * 1.8 + max_hp * 0.2 + bonus_boss_dmg * 3.0)
	}

func get_power() -> int:
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
		if item_id == "gold":
			player["gold"] = int(player.get("gold", 0)) + count
		else:
			add_item(item_id, count, false)
	restore_hp()
	emit_signal("changed")

func restore_hp() -> void:
	player["hp"] = int(get_total_stats().get("max_hp", player.get("max_hp", 850)))

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

func is_feature_unlocked(feature: Dictionary) -> bool:
	var unlock_condition: Dictionary = feature.get("unlock_condition", {})
	return get_level() >= int(unlock_condition.get("level", 1))
