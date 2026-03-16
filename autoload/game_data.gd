extends Node

signal changed
signal loaded

const LOCAL_BOOTSTRAP_PATH := "res://data/bootstrap_state.json"

var raw_bootstrap: Dictionary = {}
var character_classes: Array = []
var hall_features: Array = []
var skills: Array = []
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
var items: Array = []
var reward_groups: Dictionary = {}
var encounters: Dictionary = {}
var runtime_auth: Dictionary = {}
var runtime_player_init: Dictionary = {}
var runtime_inventory: Dictionary = {}
var runtime_stage_chapters: Array = []
var runtime_stage_nodes: Dictionary = {}
var runtime_stage_difficulties: Dictionary = {}
var runtime_dungeon_list: Array = []
var runtime_dungeon_details: Dictionary = {}
var using_runtime_backend := false
var item_labels := {
	"gold": "灵石",
	"boss_core_qingqiu": "青丘妖核",
	"boss_core_thunder": "雷鸣核心",
	"material_star_stone": "升星石",
	"material_refine_sand": "洗练砂",
	"skill_book_thunder": "雷系技能书"
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
	GameApi.reset_auth()
	var local_bootstrap := _load_local_bootstrap()
	raw_bootstrap = local_bootstrap.duplicate(true)
	_apply_bootstrap(local_bootstrap)
	PlayerState.load_from_dict(local_bootstrap.get("player", {}))

	var remote_bundle := await GameApi.fetch_runtime_bundle(local_bootstrap)
	_merge_remote_bundle(remote_bundle)

	runtime_auth = await GameApi.login(local_bootstrap.get("player", {}))
	using_runtime_backend = not runtime_auth.is_empty()
	if using_runtime_backend:
		await refresh_runtime_state(false)
	else:
		# Fallback only: backend不可用时保留 bootstrap 作为开发兜底。
		PlayerState.load_from_dict(raw_bootstrap.get("player", {}))

	_loaded_once = true
	_loading = false
	emit_signal("changed")
	emit_signal("loaded")

func refresh_runtime_state(emit_changed: bool = true) -> void:
	if not using_runtime_backend:
		if emit_changed:
			emit_signal("changed")
		return

	var init_payload := await GameApi.fetch_player_init()
	if init_payload.is_empty():
		# Fallback only: 运行态接口失败时继续保留本地状态，不覆盖现有可玩闭环。
		if emit_changed:
			emit_signal("changed")
		return

	runtime_player_init = init_payload.duplicate(true)
	_apply_runtime_player_init(init_payload)

	runtime_inventory = await GameApi.fetch_inventory()
	if runtime_inventory.is_empty():
		runtime_inventory = {
			"items": init_payload.get("inventory", []).duplicate(true),
			"currencies": {
				"gold": int(init_payload.get("player", {}).get("gold", 0)),
				"jade": int(init_payload.get("player", {}).get("jade", 0)),
				"contribution": int(init_payload.get("player", {}).get("contribution", 0))
			}
		}
	else:
		_apply_runtime_inventory(runtime_inventory)

	var stage_chapters := await GameApi.fetch_stage_chapter_list(chapters)
	if not stage_chapters.is_empty():
		runtime_stage_chapters = stage_chapters.duplicate(true)
		_merge_runtime_stage_chapters(stage_chapters)

	var dungeon_list := await GameApi.fetch_dungeon_list(dungeons)
	if not dungeon_list.is_empty():
		runtime_dungeon_list = dungeon_list.duplicate(true)
		_merge_runtime_dungeons(dungeon_list)

	if emit_changed:
		emit_signal("changed")

func commit_class_selection(class_id: String) -> bool:
	if using_runtime_backend:
		var payload := await GameApi.select_class(class_id)
		if not payload.is_empty():
			runtime_player_init = payload.duplicate(true)
			_apply_runtime_player_init(payload)
			await refresh_runtime_state(false)
			emit_signal("changed")
			return true

	# Fallback only: 正常正式态应以后端返回为准，本地仅作开发兜底。
	PlayerState.select_class(class_id)
	emit_signal("changed")
	return false

func load_stage_runtime_for_selection(node_id: String) -> void:
	if not using_runtime_backend or node_id.is_empty():
		return

	var detail := await GameApi.fetch_stage_node_detail(node_id)
	if not detail.is_empty():
		runtime_stage_nodes[node_id] = detail.duplicate(true)
		_merge_runtime_stage_node_detail(detail)

	var difficulties := await GameApi.fetch_stage_difficulty_list(node_id)
	if not difficulties.is_empty():
		runtime_stage_difficulties[node_id] = difficulties.duplicate(true)
		_merge_runtime_stage_difficulties(node_id, difficulties)

	emit_signal("changed")

func load_dungeon_runtime_detail(dungeon_id: String) -> void:
	if not using_runtime_backend or dungeon_id.is_empty():
		return

	var detail := await GameApi.fetch_dungeon_detail(dungeon_id)
	if detail.is_empty():
		return

	runtime_dungeon_details[dungeon_id] = detail.duplicate(true)
	_merge_runtime_dungeon_detail(detail)
	emit_signal("changed")

func get_open_classes() -> Array:
	return character_classes.filter(func(entry: Dictionary) -> bool: return bool(entry.get("is_open", false)))

func get_character_class(class_id: String) -> Dictionary:
	return _find_first(character_classes, "class_id", class_id)

func get_character_class_name(class_id: String) -> String:
	var data := get_character_class(class_id)
	return str(data.get("class_name", _label_from_id(class_id)))

func get_skill(skill_id: String) -> Dictionary:
	return _find_first(skills, "skill_id", skill_id)

func get_skills_for_class(class_id: String, include_closed: bool = false) -> Array:
	var filtered := skills.filter(func(entry: Dictionary) -> bool:
		return str(entry.get("class_id", "")) == class_id and (include_closed or bool(entry.get("is_open", true)))
	)
	filtered.sort_custom(func(a: Dictionary, b: Dictionary) -> bool:
		var type_weight_a := 0 if str(a.get("type", "")) == "active" else 1
		var type_weight_b := 0 if str(b.get("type", "")) == "active" else 1
		if type_weight_a != type_weight_b:
			return type_weight_a < type_weight_b
		if int(a.get("unlock_level", 1)) != int(b.get("unlock_level", 1)):
			return int(a.get("unlock_level", 1)) < int(b.get("unlock_level", 1))
		return str(a.get("skill_id", "")) < str(b.get("skill_id", ""))
	)
	return filtered

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
	if not result.has("difficulties"):
		result["difficulties"] = get_difficulties_for_dungeon(dungeon_id)
	return result

func get_difficulties_for_dungeon(dungeon_id: String) -> Array:
	var dungeon := _find_first(dungeons, "dungeon_id", dungeon_id)
	if not dungeon.is_empty() and dungeon.has("difficulties"):
		return dungeon.get("difficulties", []).duplicate(true)
	var entries := dungeon_difficulties.filter(func(entry: Dictionary) -> bool: return str(entry.get("dungeon_id", "")) == dungeon_id)
	entries.sort_custom(func(a: Dictionary, b: Dictionary) -> bool:
		return _difficulty_order(str(a.get("difficulty_id", ""))) < _difficulty_order(str(b.get("difficulty_id", "")))
	)
	return entries

func get_monster(monster_id: String) -> Dictionary:
	return _find_first(monsters, "monster_id", monster_id)

func get_item(item_id: String) -> Dictionary:
	return _find_first(items, "item_id", item_id)

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

	var item_data := get_item(item_id)
	if not item_data.is_empty():
		return item_data.duplicate(true)

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
	var rewards = reward_groups.get(group_id, [])
	if rewards is Array:
		return rewards.duplicate(true)
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
	skills = source.get("skills", []).duplicate(true)
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
	items = source.get("items", []).duplicate(true)
	reward_groups = source.get("reward_groups", {}).duplicate(true)
	encounters = source.get("encounters", {}).duplicate(true)
	runtime_player_init.clear()
	runtime_inventory.clear()
	runtime_stage_chapters.clear()
	runtime_stage_nodes.clear()
	runtime_stage_difficulties.clear()
	runtime_dungeon_list.clear()
	runtime_dungeon_details.clear()
	_fill_default_content()

func _merge_remote_bundle(remote_bundle: Dictionary) -> void:
	if remote_bundle.has("character_classes"):
		character_classes = remote_bundle.get("character_classes", []).duplicate(true)
	if remote_bundle.has("hall_features"):
		hall_features = remote_bundle.get("hall_features", []).duplicate(true)
	if remote_bundle.has("skills"):
		skills = remote_bundle.get("skills", []).duplicate(true)
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

func _apply_runtime_player_init(payload: Dictionary) -> void:
	var player_payload: Dictionary = payload.get("player", {}).duplicate(true)
	if player_payload.is_empty():
		return
	if not player_payload.has("inventory"):
		player_payload["inventory"] = payload.get("inventory", []).duplicate(true)
	PlayerState.load_from_dict(player_payload)

func _apply_runtime_inventory(payload: Dictionary) -> void:
	var merged_player: Dictionary = PlayerState.player.duplicate(true)
	merged_player["inventory"] = payload.get("items", []).duplicate(true)
	var currencies: Dictionary = payload.get("currencies", {})
	merged_player["gold"] = int(currencies.get("gold", merged_player.get("gold", 0)))
	merged_player["jade"] = int(currencies.get("jade", merged_player.get("jade", 0)))
	merged_player["contribution"] = int(currencies.get("contribution", merged_player.get("contribution", 0)))
	PlayerState.load_from_dict(merged_player)

func _merge_runtime_stage_chapters(stage_chapters: Array) -> void:
	var chapter_lookup := {}
	for chapter in chapters:
		chapter_lookup[str(chapter.get("chapter_id", ""))] = chapter.duplicate(true)

	var merged: Array = []
	for runtime_chapter in stage_chapters:
		var chapter_id := str(runtime_chapter.get("chapter_id", ""))
		var base_chapter: Dictionary = chapter_lookup.get(chapter_id, {})
		var normalized := base_chapter.duplicate(true)
		for key in runtime_chapter.keys():
			if key == "nodes":
				continue
			normalized[key] = runtime_chapter.get(key)
		normalized["nodes"] = _merge_runtime_nodes(
			base_chapter.get("nodes", []),
			runtime_chapter.get("nodes", []),
		)
		merged.append(normalized)
		chapter_lookup.erase(chapter_id)

	for chapter_id in chapter_lookup.keys():
		merged.append(chapter_lookup[chapter_id])

	chapters = merged

func _merge_runtime_nodes(base_nodes: Array, runtime_nodes: Array) -> Array:
	var node_lookup := {}
	for node in base_nodes:
		node_lookup[str(node.get("node_id", ""))] = node.duplicate(true)

	var merged_nodes: Array = []
	for runtime_node in runtime_nodes:
		var node_id := str(runtime_node.get("node_id", ""))
		var base_node: Dictionary = node_lookup.get(node_id, {})
		var normalized := base_node.duplicate(true)
		for key in runtime_node.keys():
			if key == "difficulties":
				continue
			normalized[key] = runtime_node.get(key)
		if runtime_node.has("difficulties"):
			normalized["difficulties"] = runtime_node.get("difficulties", []).duplicate(true)
		elif base_node.has("difficulties"):
			normalized["difficulties"] = base_node.get("difficulties", []).duplicate(true)
		merged_nodes.append(normalized)
		node_lookup.erase(node_id)

	for node_id in node_lookup.keys():
		merged_nodes.append(node_lookup[node_id])

	return merged_nodes

func _merge_runtime_stage_node_detail(detail: Dictionary) -> void:
	var node: Dictionary = detail.get("node", {})
	var node_id := str(node.get("node_id", ""))
	if node_id.is_empty():
		return
	for chapter_index in chapters.size():
		var chapter: Dictionary = chapters[chapter_index]
		var chapter_nodes: Array = chapter.get("nodes", [])
		for node_index in chapter_nodes.size():
			if str(chapter_nodes[node_index].get("node_id", "")) != node_id:
				continue
			var merged_node: Dictionary = chapter_nodes[node_index].duplicate(true)
			for key in node.keys():
				merged_node[key] = node.get(key)
			chapter_nodes[node_index] = merged_node
			chapter["nodes"] = chapter_nodes
			if detail.has("chapter_name"):
				chapter["chapter_name"] = detail.get("chapter_name")
			chapters[chapter_index] = chapter
			return

func _merge_runtime_stage_difficulties(node_id: String, difficulties: Array) -> void:
	for chapter_index in chapters.size():
		var chapter: Dictionary = chapters[chapter_index]
		var chapter_nodes: Array = chapter.get("nodes", [])
		for node_index in chapter_nodes.size():
			if str(chapter_nodes[node_index].get("node_id", "")) != node_id:
				continue
			var merged_node: Dictionary = chapter_nodes[node_index].duplicate(true)
			merged_node["difficulties"] = difficulties.duplicate(true)
			chapter_nodes[node_index] = merged_node
			chapter["nodes"] = chapter_nodes
			chapters[chapter_index] = chapter
			return

func _merge_runtime_dungeons(runtime_dungeons: Array) -> void:
	var dungeon_lookup := {}
	for dungeon in dungeons:
		dungeon_lookup[str(dungeon.get("dungeon_id", ""))] = dungeon.duplicate(true)

	var merged: Array = []
	for runtime_dungeon in runtime_dungeons:
		var dungeon_id := str(runtime_dungeon.get("dungeon_id", ""))
		var normalized: Dictionary = dungeon_lookup.get(dungeon_id, {}).duplicate(true)
		for key in runtime_dungeon.keys():
			if key == "difficulties":
				continue
			normalized[key] = runtime_dungeon.get(key)
		if runtime_dungeon.has("difficulties"):
			normalized["difficulties"] = runtime_dungeon.get("difficulties", []).duplicate(true)
		merged.append(normalized)
		dungeon_lookup.erase(dungeon_id)

	for dungeon_id in dungeon_lookup.keys():
		merged.append(dungeon_lookup[dungeon_id])

	dungeons = merged

func _merge_runtime_dungeon_detail(detail: Dictionary) -> void:
	var dungeon: Dictionary = detail.get("dungeon", {})
	var dungeon_id := str(dungeon.get("dungeon_id", ""))
	if dungeon_id.is_empty():
		return
	for index in dungeons.size():
		if str(dungeons[index].get("dungeon_id", "")) != dungeon_id:
			continue
		var merged_dungeon: Dictionary = dungeons[index].duplicate(true)
		for key in dungeon.keys():
			merged_dungeon[key] = dungeon.get(key)
		dungeons[index] = merged_dungeon
		return
	dungeons.append(dungeon.duplicate(true))

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
			"first_clear_reward_group_id": str(difficulty.get("first_clear_reward_group_id", "")),
			"is_unlocked": true,
			"is_first_clear": false,
			"clear_count": 0
		})

	for node in payload.get("node_config", []):
		var normalized_node := {
			"node_id": str(node.get("node_id", "")),
			"node_name": node.get("node_name", "未命名节点"),
			"unlock_condition": node.get("unlock_condition", {"level": 1}),
			"difficulty_ids": node.get("difficulty_ids", []).duplicate(true),
			"is_unlocked": true,
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
		"nightmare":
			return 3
		_:
			return 99

func _label_from_id(raw_id: String) -> String:
	return raw_id.replace("_", " ").capitalize()
