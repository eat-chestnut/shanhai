extends Node

signal request_log(message: String)

const DEFAULT_BASE_URL := "http://127.0.0.1:8000/api/v1"

var base_url := DEFAULT_BASE_URL
var auth_token := ""
var last_api_error: Dictionary = {}

func _ready() -> void:
	var env_base := OS.get_environment("SHANHAI_API_URL").strip_edges()
	if not env_base.is_empty():
		base_url = env_base

func reset_auth() -> void:
	auth_token = ""
	last_api_error = {}

func clear_last_error() -> void:
	last_api_error = {}

func get_last_error_message() -> String:
	return str(last_api_error.get("msg", ""))

func is_business_error() -> bool:
	return str(last_api_error.get("type", "")) == "business"

func can_use_transport_fallback() -> bool:
	return str(last_api_error.get("type", "")) == "transport"

func fetch_runtime_bundle(local_fallback: Dictionary) -> Dictionary:
	var bundle := local_fallback.duplicate(true)
	bundle["character_classes"] = await fetch_character_classes(local_fallback.get("character_classes", []))
	bundle["hall_features"] = await fetch_hall_features(local_fallback.get("hall_features", []))
	bundle["skills"] = await fetch_skills(local_fallback.get("skills", []))
	bundle["mainline_config"] = await fetch_mainline_config(local_fallback)
	bundle["dungeon_content_config"] = await fetch_dungeon_content_config(local_fallback)
	bundle["equipment_config"] = await fetch_equipment_config(local_fallback)
	bundle["items"] = await fetch_items(local_fallback.get("items", []))
	bundle["rarity_configs"] = await fetch_rarity_configs(local_fallback.get("rarity_configs", []))
	return bundle

func login(player_fallback: Dictionary) -> Dictionary:
	var player_id := int(player_fallback.get("player_id", 10001))
	var nickname := str(player_fallback.get("nickname", "巡厄弟子 %s" % player_id))
	var data := await _request_api_data(
		"/auth/login",
		HTTPClient.METHOD_POST,
		{
			"player_id": player_id,
			"nickname": nickname
		},
		false
	)
	if not data.is_empty():
		auth_token = str(data.get("token", ""))
	return data

func fetch_player_init() -> Dictionary:
	return await _request_api_data("/player/init", HTTPClient.METHOD_GET, {}, true)

func select_class(class_id: String) -> Dictionary:
	return await _request_api_data(
		"/class/select",
		HTTPClient.METHOD_POST,
		{"class_id": class_id},
		true
	)

func fetch_stage_chapter_list(fallback: Array) -> Array:
	var data := await _request_api_data("/stage/chapter/list", HTTPClient.METHOD_GET, {}, true)
	var chapters = data.get("chapters", [])
	if chapters is Array:
		return chapters.duplicate(true)
	return fallback.duplicate(true)

func fetch_stage_node_detail(node_id: String) -> Dictionary:
	return await _request_api_data(
		"/stage/node/detail?node_id=%s" % node_id.uri_encode(),
		HTTPClient.METHOD_GET,
		{},
		true
	)

func fetch_stage_difficulty_list(node_id: String, fallback: Array = []) -> Array:
	var data := await _request_api_data(
		"/stage/difficulty/list?node_id=%s" % node_id.uri_encode(),
		HTTPClient.METHOD_GET,
		{},
		true
	)
	var difficulties = data.get("difficulties", [])
	if difficulties is Array:
		return difficulties.duplicate(true)
	return fallback.duplicate(true)

func fetch_scripture_list() -> Dictionary:
	return await _request_api_data("/scripture/list", HTTPClient.METHOD_GET, {}, true)

func fetch_scripture_detail(scripture_id: String) -> Dictionary:
	return await _request_api_data(
		"/scripture/detail?scripture_id=%s" % scripture_id.uri_encode(),
		HTTPClient.METHOD_GET,
		{},
		true
	)

func upgrade_scripture(scripture_id: String, target_world_level: int) -> Dictionary:
	return await _request_api_data(
		"/scripture/upgrade",
		HTTPClient.METHOD_POST,
		{
			"scripture_id": scripture_id,
			"target_world_level": target_world_level
		},
		true
	)

func fetch_dungeon_list(fallback: Array) -> Array:
	var data := await _request_api_data("/dungeon/list", HTTPClient.METHOD_GET, {}, true)
	var dungeons = data.get("dungeons", [])
	if dungeons is Array:
		return dungeons.duplicate(true)
	return fallback.duplicate(true)

func fetch_dungeon_detail(dungeon_id: String) -> Dictionary:
	return await _request_api_data(
		"/dungeon/detail?dungeon_id=%s" % dungeon_id.uri_encode(),
		HTTPClient.METHOD_GET,
		{},
		true
	)

func fetch_inventory() -> Dictionary:
	return await _request_api_data("/inventory/list", HTTPClient.METHOD_GET, {}, true)

func fetch_equipment_detail(equipment_uid: String = "") -> Dictionary:
	var path := "/equipment/detail"
	if not equipment_uid.is_empty():
		path += "?equipment_uid=%s" % equipment_uid.uri_encode()
	return await _request_api_data(path, HTTPClient.METHOD_GET, {}, true)

func equip_equipment(equipment_uid: String) -> Dictionary:
	return await _request_api_data("/equipment/equip", HTTPClient.METHOD_POST, {"equipment_uid": equipment_uid}, true)

func unequip_equipment(equipment_uid: String) -> Dictionary:
	return await _request_api_data("/equipment/unequip", HTTPClient.METHOD_POST, {"equipment_uid": equipment_uid}, true)

func star_up_equipment(equipment_uid: String) -> Dictionary:
	return await _request_api_data("/equipment/star_up", HTTPClient.METHOD_POST, {"equipment_uid": equipment_uid}, true)

func socket_gem(equipment_uid: String, gem_id: String, slot_index: int) -> Dictionary:
	return await _request_api_data(
		"/equipment/socket_gem",
		HTTPClient.METHOD_POST,
		{
			"equipment_uid": equipment_uid,
			"gem_id": gem_id,
			"slot_index": slot_index
		},
		true
	)

func extract_blue_affix(equipment_uid: String) -> Dictionary:
	return await _request_api_data(
		"/equipment/extract_blue_affix",
		HTTPClient.METHOD_POST,
		{"equipment_uid": equipment_uid},
		true
	)

func refine_purple_affix(equipment_uid: String) -> Dictionary:
	return await _request_api_data(
		"/equipment/refine_purple_affix",
		HTTPClient.METHOD_POST,
		{"equipment_uid": equipment_uid},
		true
	)

func fetch_task_list() -> Dictionary:
	return await _request_api_data("/task/list", HTTPClient.METHOD_GET, {}, true)

func claim_task(task_id: String) -> Dictionary:
	return await _request_api_data("/task/claim", HTTPClient.METHOD_POST, {"task_id": task_id}, true)

func claim_all_tasks() -> Dictionary:
	return await _request_api_data("/task/claim_all", HTTPClient.METHOD_POST, {}, true)

func fetch_common_shop_list() -> Dictionary:
	return await _request_api_data("/shop/common/list", HTTPClient.METHOD_GET, {}, true)

func buy_common_shop_item(shop_item_id: String, count: int = 1) -> Dictionary:
	return await _request_api_data(
		"/shop/common/buy",
		HTTPClient.METHOD_POST,
		{"shop_item_id": shop_item_id, "count": count},
		true
	)

func fetch_sect_shop_list() -> Dictionary:
	return await _request_api_data("/shop/sect/list", HTTPClient.METHOD_GET, {}, true)

func buy_sect_shop_item(shop_item_id: String, count: int = 1) -> Dictionary:
	return await _request_api_data(
		"/shop/sect/buy",
		HTTPClient.METHOD_POST,
		{"shop_item_id": shop_item_id, "count": count},
		true
	)

func fetch_idle_status() -> Dictionary:
	return await _request_api_data("/idle/status", HTTPClient.METHOD_GET, {}, true)

func claim_idle_rewards() -> Dictionary:
	return await _request_api_data("/idle/claim", HTTPClient.METHOD_POST, {}, true)

func fetch_idle_rules() -> Dictionary:
	return await _request_api_data("/idle/rules", HTTPClient.METHOD_GET, {}, true)

func fetch_challenge_list() -> Dictionary:
	return await _request_api_data("/challenge/list", HTTPClient.METHOD_GET, {}, true)

func fetch_challenge_detail(challenge_id: String) -> Dictionary:
	return await _request_api_data(
		"/challenge/detail?challenge_id=%s" % challenge_id.uri_encode(),
		HTTPClient.METHOD_GET,
		{},
		true
	)

func battle_prepare(source_type: String, source_id: String, difficulty_id: String = "", world_level: int = 0) -> Dictionary:
	var payload := {
		"source_type": source_type,
		"source_id": source_id
	}
	if not difficulty_id.is_empty():
		payload["difficulty_id"] = difficulty_id
	if world_level > 0:
		payload["world_level"] = world_level
	return await _request_api_data("/battle/prepare", HTTPClient.METHOD_POST, payload, true)

func battle_settle(payload: Dictionary) -> Dictionary:
	return await _request_api_data("/battle/settle", HTTPClient.METHOD_POST, payload, true)

func fetch_character_classes(fallback: Array) -> Array:
	var payload := await _request_json_raw("/character-classes?per_page=50")
	if payload.is_empty():
		return fallback.duplicate(true)
	return _extract_resource_list(payload, fallback)

func fetch_hall_features(fallback: Array) -> Array:
	var payload := await _request_json_raw("/hall-features?per_page=50")
	if payload.is_empty():
		return fallback.duplicate(true)
	return _extract_resource_list(payload, fallback)

func fetch_skills(fallback: Array) -> Array:
	var payload := await _request_json_raw("/skills?per_page=100&sort_by=class_id&sort_direction=asc")
	if payload.is_empty():
		return fallback.duplicate(true)
	return _extract_resource_list(payload, fallback)

func fetch_mainline_config(local_fallback: Dictionary) -> Dictionary:
	var payload := await _request_json_raw("/mainline-config")
	if payload.is_empty():
		return {
			"chapters": local_fallback.get("chapters", []).duplicate(true)
		}
	return payload

func fetch_dungeon_content_config(local_fallback: Dictionary) -> Dictionary:
	var payload := await _request_json_raw("/dungeon-content-config")
	if payload.is_empty():
		return {
			"dungeons": local_fallback.get("dungeons", []).duplicate(true),
			"dungeon_difficulties": local_fallback.get("dungeon_difficulties", []).duplicate(true),
			"monsters": local_fallback.get("monsters", []).duplicate(true),
			"monster_drops": local_fallback.get("monster_drops", []).duplicate(true)
		}
	return payload

func fetch_equipment_config(local_fallback: Dictionary) -> Dictionary:
	var payload := await _request_json_raw("/equipment-config")
	if payload.is_empty():
		return {
			"equipment": local_fallback.get("equipment", []).duplicate(true),
			"equipment_sets": local_fallback.get("equipment_sets", []).duplicate(true),
			"gems": local_fallback.get("gems", []).duplicate(true),
			"blue_affixes": local_fallback.get("blue_affixes", []).duplicate(true),
			"purple_refinements": local_fallback.get("purple_refinements", []).duplicate(true)
		}
	return payload

func _extract_resource_list(payload: Dictionary, fallback: Array) -> Array:
	var data = payload.get("data", [])
	if data is Array:
		return data.duplicate(true)
	return fallback.duplicate(true)

func _request_api_data(path: String, method: int, body: Dictionary = {}, requires_auth: bool = false) -> Dictionary:
	last_api_error = {}
	var payload := await _request_json_raw(path, method, body, requires_auth)
	if payload.is_empty():
		return {}
	if int(payload.get("code", -1)) != 0:
		last_api_error = {
			"type": "business",
			"path": path,
			"code": int(payload.get("code", -1)),
			"msg": str(payload.get("msg", "unknown"))
		}
		emit_signal("request_log", "API business error for %s: %s" % [path, str(payload.get("msg", "unknown"))])
		return {}
	var data = payload.get("data", {})
	if data is Dictionary:
		return data.duplicate(true)
	return {}

func _request_json_raw(path: String, method: int = HTTPClient.METHOD_GET, body: Dictionary = {}, requires_auth: bool = false) -> Dictionary:
	var http := HTTPRequest.new()
	add_child(http)
	http.timeout = 6.0

	var headers: PackedStringArray = ["Accept: application/json"]
	if requires_auth and not auth_token.is_empty():
		headers.append("Authorization: Bearer %s" % auth_token)

	var payload_body := ""
	if method != HTTPClient.METHOD_GET:
		headers.append("Content-Type: application/json")
		payload_body = JSON.stringify(body)

	var error := http.request(_build_url(path), headers, method, payload_body)
	if error != OK:
		last_api_error = {
			"type": "transport",
			"path": path,
			"code": error,
			"msg": "接口请求失败"
		}
		emit_signal("request_log", "API request skipped for %s (%s)." % [path, error])
		remove_child(http)
		http.call_deferred("free")
		return {}

	var response: Array = await http.request_completed
	remove_child(http)
	http.call_deferred("free")

	if response.size() < 4:
		last_api_error = {
			"type": "transport",
			"path": path,
			"code": -2,
			"msg": "接口响应格式异常"
		}
		emit_signal("request_log", "API response malformed for %s." % path)
		return {}

	var result_code := int(response[0])
	var status_code := int(response[1])
	var body_bytes: PackedByteArray = response[3]

	if result_code != HTTPRequest.RESULT_SUCCESS or status_code < 200 or status_code >= 300:
		last_api_error = {
			"type": "transport",
			"path": path,
			"code": status_code,
			"msg": "接口请求失败"
		}
		emit_signal("request_log", "API request failed for %s (%s/%s)." % [path, result_code, status_code])
		return {}

	var parsed = JSON.parse_string(body_bytes.get_string_from_utf8())
	if parsed is Dictionary:
		return parsed

	last_api_error = {
		"type": "transport",
		"path": path,
		"code": -3,
		"msg": "接口返回不是 JSON"
	}
	emit_signal("request_log", "API payload for %s is not a JSON object." % path)
	return {}

func _build_url(path: String) -> String:
	var normalized_base := base_url.rstrip("/")
	var normalized_path := path if path.begins_with("/") else "/%s" % path
	return "%s%s" % [normalized_base, normalized_path]

func fetch_items(fallback: Array) -> Array:
	var data := await _request_api_data("/items/list", HTTPClient.METHOD_GET, {}, true)
	var items = data.get("items", [])
	if items is Array:
		return items.duplicate(true)
	return fallback.duplicate(true)

func fetch_rarity_configs(fallback: Array) -> Array:
	var data := await _request_api_data("/rarity-configs/list", HTTPClient.METHOD_GET, {}, true)
	var configs = data.get("rarity_configs", [])
	if configs is Array:
		return configs.duplicate(true)
	return fallback.duplicate(true)
