extends Node

signal changed
signal loaded

const LOCAL_BOOTSTRAP_PATH := "res://data/bootstrap_state.json"

var raw_bootstrap: Dictionary = {}
var character_classes: Array = []
var hall_features: Array = []
var chapters: Array = []
var dungeons: Array = []
var dungeon_difficulties: Array = []
var monsters: Array = []
var monster_drops: Array = []
var equipment: Array = []
var equipment_sets: Array = []
var gems: Array = []
var blue_affixes: Array = []
var purple_refinements: Array = []
var reward_groups: Dictionary = {}
var encounters: Dictionary = {}
var item_labels := {
	"gold": "灵石",
	"boss_core_qingqiu": "青丘妖核"
}

var _loaded_once := false
var _loading := false

func load_all(force_reload: bool = false) -> void:
	if _loading:
		return
	if _loaded_once and not force_reload:
		emit_signal("loaded")
		return

	_loading = true
	var local_bootstrap := _load_local_bootstrap()
	raw_bootstrap = local_bootstrap.duplicate(true)
	_apply_bootstrap(local_bootstrap)
	PlayerState.load_from_dict(local_bootstrap.get("player", {}))

	var remote_bundle := await GameApi.fetch_runtime_bundle(local_bootstrap)
	_merge_remote_bundle(remote_bundle)

	_loaded_once = true
	_loading = false
	emit_signal("changed")
	emit_signal("loaded")

func get_open_classes() -> Array:
	return character_classes.filter(func(entry: Dictionary) -> bool: return bool(entry.get("is_open", false)))

func get_character_class(class_id: String) -> Dictionary:
	return _find_first(character_classes, "class_id", class_id)

func get_character_class_name(class_id: String) -> String:
	var data := get_character_class(class_id)
	return str(data.get("class_name", _label_from_id(class_id)))

func get_chapter(chapter_id: String) -> Dictionary:
	return _find_first(chapters, "chapter_id", chapter_id)

func get_mainline_node(node_id: String) -> Dictionary:
	for chapter in chapters:
		for node in chapter.get("nodes", []):
			if str(node.get("node_id", "")) == node_id:
				return node
	return {}

func get_difficulty_for_node(node_id: String, difficulty_id: String) -> Dictionary:
	for difficulty in get_mainline_node(node_id).get("difficulties", []):
		if str(difficulty.get("difficulty_id", "")) == difficulty_id:
			return difficulty
	return {}

func get_dungeon(dungeon_id: String) -> Dictionary:
	var dungeon := _find_first(dungeons, "dungeon_id", dungeon_id)
	if dungeon.is_empty():
		return {}
	var result := dungeon.duplicate(true)
	result["difficulties"] = get_difficulties_for_dungeon(dungeon_id)
	return result

func get_difficulties_for_dungeon(dungeon_id: String) -> Array:
	var items := dungeon_difficulties.filter(func(entry: Dictionary) -> bool: return str(entry.get("dungeon_id", "")) == dungeon_id)
	items.sort_custom(func(a: Dictionary, b: Dictionary) -> bool:
		return _difficulty_order(str(a.get("difficulty_id", ""))) < _difficulty_order(str(b.get("difficulty_id", "")))
	)
	return items

func get_monster(monster_id: String) -> Dictionary:
	return _find_first(monsters, "monster_id", monster_id)

func get_equipment(equip_id: String) -> Dictionary:
	return _find_first(equipment, "equip_id", equip_id)

func get_gem(gem_id: String) -> Dictionary:
	return _find_first(gems, "gem_id", gem_id)

func get_blue_affix(affix_id: String) -> Dictionary:
	return _find_first(blue_affixes, "affix_id", affix_id)

func get_purple_refinement(refinement_id: String) -> Dictionary:
	return _find_first(purple_refinements, "refinement_id", refinement_id)

func get_set(set_id: String) -> Dictionary:
	return _find_first(equipment_sets, "set_id", set_id)

func get_item_definition(item_id: String) -> Dictionary:
	if item_id == "gold":
		return {"item_id": "gold", "name": item_labels["gold"], "type": "currency"}

	var equipment_data := get_equipment(item_id)
	if not equipment_data.is_empty():
		return {"item_id": item_id, "name": equipment_data.get("name", item_id), "type": equipment_data.get("type", "equipment")}

	var gem_data := get_gem(item_id)
	if not gem_data.is_empty():
		return {"item_id": item_id, "name": gem_data.get("name", item_id), "type": "gem"}

	var affix_data := get_blue_affix(item_id)
	if not affix_data.is_empty():
		return {"item_id": item_id, "name": affix_data.get("name", item_id), "type": "blue_affix"}

	var refinement_data := get_purple_refinement(item_id)
	if not refinement_data.is_empty():
		return {"item_id": item_id, "name": refinement_data.get("name", item_id), "type": "purple_refinement"}

	return {"item_id": item_id, "name": item_labels.get(item_id, _label_from_id(item_id)), "type": "loot"}

func get_reward_group_items(group_id: String) -> Array:
	var items = reward_groups.get(group_id, [])
	if items is Array:
		return items.duplicate(true)
	return _default_reward_group(group_id)

func get_mainline_encounter(node_id: String) -> Array:
	for entry in encounters.get("mainline", []):
		if str(entry.get("node_id", "")) == node_id:
			return entry.get("monster_ids", []).duplicate(true)
	return _default_mainline_encounter(node_id)

func get_dungeon_encounter(dungeon_id: String) -> Array:
	for entry in encounters.get("dungeons", []):
		if str(entry.get("dungeon_id", "")) == dungeon_id:
			return entry.get("monster_ids", []).duplicate(true)
	return _default_dungeon_encounter(dungeon_id)

func _load_local_bootstrap() -> Dictionary:
	if not FileAccess.file_exists(LOCAL_BOOTSTRAP_PATH):
		return {}
	var parsed = JSON.parse_string(FileAccess.get_file_as_string(LOCAL_BOOTSTRAP_PATH))
	if parsed is Dictionary:
		return parsed
	return {}

func _apply_bootstrap(source: Dictionary) -> void:
	character_classes = source.get("character_classes", []).duplicate(true)
	hall_features = source.get("hall_features", []).duplicate(true)
	chapters = source.get("chapters", []).duplicate(true)
	dungeons = source.get("dungeons", []).duplicate(true)
	dungeon_difficulties = source.get("dungeon_difficulties", []).duplicate(true)
	monsters = source.get("monsters", []).duplicate(true)
	monster_drops = source.get("monster_drops", []).duplicate(true)
	equipment = source.get("equipment", []).duplicate(true)
	equipment_sets = source.get("equipment_sets", []).duplicate(true)
	gems = source.get("gems", []).duplicate(true)
	blue_affixes = source.get("blue_affixes", []).duplicate(true)
	purple_refinements = source.get("purple_refinements", []).duplicate(true)
	reward_groups = source.get("reward_groups", {}).duplicate(true)
	encounters = source.get("encounters", {}).duplicate(true)
	_fill_default_content()

func _merge_remote_bundle(remote_bundle: Dictionary) -> void:
	if remote_bundle.has("character_classes"):
		character_classes = remote_bundle.get("character_classes", []).duplicate(true)
	if remote_bundle.has("hall_features"):
		hall_features = remote_bundle.get("hall_features", []).duplicate(true)
	if remote_bundle.has("mainline_config"):
		chapters = _normalize_mainline(remote_bundle.get("mainline_config", {}), chapters)
	if remote_bundle.has("dungeon_content_config"):
		var dungeon_payload: Dictionary = remote_bundle.get("dungeon_content_config", {})
		dungeons = dungeon_payload.get("dungeon_config", dungeon_payload.get("dungeons", dungeons)).duplicate(true)
		dungeon_difficulties = dungeon_payload.get("dungeon_difficulty_config", dungeon_payload.get("dungeon_difficulties", dungeon_difficulties)).duplicate(true)
		monsters = dungeon_payload.get("monster_config", dungeon_payload.get("monsters", monsters)).duplicate(true)
		monster_drops = dungeon_payload.get("monster_drop_config", dungeon_payload.get("monster_drops", monster_drops)).duplicate(true)
	if remote_bundle.has("equipment_config"):
		var equipment_payload: Dictionary = remote_bundle.get("equipment_config", {})
		equipment = equipment_payload.get("equipment_config", equipment_payload.get("equipment", equipment)).duplicate(true)
		equipment_sets = equipment_payload.get("equipment_set_config", equipment_payload.get("equipment_sets", equipment_sets)).duplicate(true)
		gems = equipment_payload.get("gem_config", equipment_payload.get("gems", gems)).duplicate(true)
		blue_affixes = equipment_payload.get("blue_affix_config", equipment_payload.get("blue_affixes", blue_affixes)).duplicate(true)
		purple_refinements = equipment_payload.get("purple_refinement_config", equipment_payload.get("purple_refinements", purple_refinements)).duplicate(true)
	_fill_default_content()
	PlayerState.load_from_dict(raw_bootstrap.get("player", {}))

func _normalize_mainline(payload: Dictionary, fallback: Array) -> Array:
	if payload.is_empty():
		return fallback.duplicate(true)

	if payload.has("chapters"):
		return payload.get("chapters", []).duplicate(true)

	var chapter_lookup := {}
	var ordered: Array = []

	for chapter in payload.get("chapter_config", []):
		var chapter_id := str(chapter.get("chapter_id", ""))
		var normalized := {
			"chapter_id": chapter_id,
			"chapter_name": chapter.get("chapter_name", chapter_id),
			"unlock_level": int(chapter.get("unlock_level", 1)),
			"nodes": []
		}
		chapter_lookup[chapter_id] = normalized
		ordered.append(normalized)

	var difficulty_map := {}
	for difficulty in payload.get("difficulty_config", []):
		var node_id := str(difficulty.get("node_id", ""))
		if not difficulty_map.has(node_id):
			difficulty_map[node_id] = []
		difficulty_map[node_id].append({
			"difficulty_id": str(difficulty.get("difficulty_id", "")),
			"recommended_power": int(difficulty.get("recommended_power", 0)),
			"first_clear_reward_group_id": str(difficulty.get("first_clear_reward_group_id", ""))
		})

	for node in payload.get("node_config", []):
		var normalized_node := {
			"node_id": str(node.get("node_id", "")),
			"node_name": node.get("node_name", "未命名节点"),
			"unlock_condition": node.get("unlock_condition", {"level": 1}),
			"difficulties": difficulty_map.get(str(node.get("node_id", "")), [])
		}
		var owner_id := str(node.get("chapter_id", ""))
		if chapter_lookup.has(owner_id):
			var owner: Dictionary = chapter_lookup[owner_id]
			var owner_nodes: Array = owner.get("nodes", [])
			owner_nodes.append(normalized_node)
			owner["nodes"] = owner_nodes
			chapter_lookup[owner_id] = owner

	for index in ordered.size():
		var chapter_id := str(ordered[index].get("chapter_id", ""))
		ordered[index] = chapter_lookup.get(chapter_id, ordered[index])

	return ordered

func _fill_default_content() -> void:
	if reward_groups.is_empty():
		reward_groups = {
			"reward_node01_easy": [{"item_id": "gold", "count": 100}],
			"reward_node01_normal": [{"item_id": "gold", "count": 180}],
			"reward_node01_hard": [{"item_id": "gold", "count": 300}]
		}
	if encounters.is_empty():
		encounters = {
			"mainline": [{"node_id": "node_01", "monster_ids": _default_mainline_encounter("node_01")}],
			"dungeons": [{"dungeon_id": "dungeon_gem", "monster_ids": _default_dungeon_encounter("dungeon_gem")}]
		}

func _default_reward_group(group_id: String) -> Array:
	if group_id.is_empty():
		return []
	return [{"item_id": "gold", "count": 120}]

func _default_mainline_encounter(_node_id: String) -> Array:
	if monsters.size() >= 2:
		return [monsters[0].get("monster_id", ""), monsters[0].get("monster_id", ""), monsters[1].get("monster_id", "")]
	return monsters.map(func(entry: Dictionary) -> String: return str(entry.get("monster_id", "")))

func _default_dungeon_encounter(dungeon_id: String) -> Array:
	if dungeon_id == "dungeon_refine" and monsters.size() >= 2:
		return [monsters[1].get("monster_id", "")]
	return _default_mainline_encounter("")

func _find_first(source: Array, key: String, value: String) -> Dictionary:
	for entry in source:
		if str(entry.get(key, "")) == value:
			return entry
	return {}

func _difficulty_order(difficulty_id: String) -> int:
	match difficulty_id:
		"easy":
			return 0
		"normal":
			return 1
		"hard":
			return 2
		_:
			return 99

func _label_from_id(raw_id: String) -> String:
	return raw_id.replace("_", " ").capitalize()
