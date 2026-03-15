extends Node

signal request_log(message: String)

const DEFAULT_BASE_URL := "http://127.0.0.1:8000/api/v1"

var base_url := DEFAULT_BASE_URL

func _ready() -> void:
	var env_base := OS.get_environment("SHANHAI_API_URL").strip_edges()
	if not env_base.is_empty():
		base_url = env_base

func fetch_runtime_bundle(local_fallback: Dictionary) -> Dictionary:
	var bundle := local_fallback.duplicate(true)
	bundle["character_classes"] = await fetch_character_classes(local_fallback.get("character_classes", []))
	bundle["hall_features"] = await fetch_hall_features(local_fallback.get("hall_features", []))
	bundle["mainline_config"] = await fetch_mainline_config(local_fallback)
	bundle["dungeon_content_config"] = await fetch_dungeon_content_config(local_fallback)
	bundle["equipment_config"] = await fetch_equipment_config(local_fallback)
	return bundle

func fetch_character_classes(fallback: Array) -> Array:
	var payload := await _request_json("/character-classes?per_page=50")
	if payload.is_empty():
		return fallback.duplicate(true)
	return _extract_resource_list(payload, fallback)

func fetch_hall_features(fallback: Array) -> Array:
	var payload := await _request_json("/hall-features?per_page=50")
	if payload.is_empty():
		return fallback.duplicate(true)
	return _extract_resource_list(payload, fallback)

func fetch_mainline_config(local_fallback: Dictionary) -> Dictionary:
	var payload := await _request_json("/mainline-config")
	if payload.is_empty():
		return {
			"chapters": local_fallback.get("chapters", []).duplicate(true)
		}
	return payload

func fetch_dungeon_content_config(local_fallback: Dictionary) -> Dictionary:
	var payload := await _request_json("/dungeon-content-config")
	if payload.is_empty():
		return {
			"dungeons": local_fallback.get("dungeons", []).duplicate(true),
			"dungeon_difficulties": local_fallback.get("dungeon_difficulties", []).duplicate(true),
			"monsters": local_fallback.get("monsters", []).duplicate(true),
			"monster_drops": local_fallback.get("monster_drops", []).duplicate(true)
		}
	return payload

func fetch_equipment_config(local_fallback: Dictionary) -> Dictionary:
	var payload := await _request_json("/equipment-config")
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

func _request_json(path: String) -> Dictionary:
	var http := HTTPRequest.new()
	add_child(http)
	http.timeout = 6.0
	var error := http.request(_build_url(path), ["Accept: application/json"], HTTPClient.METHOD_GET)
	if error != OK:
		emit_signal("request_log", "API request skipped for %s (%s)." % [path, error])
		remove_child(http)
		http.call_deferred("free")
		return {}

	var response: Array = await http.request_completed
	remove_child(http)
	http.call_deferred("free")

	if response.size() < 4:
		emit_signal("request_log", "API response malformed for %s." % path)
		return {}

	var result_code := int(response[0])
	var status_code := int(response[1])
	var body_bytes: PackedByteArray = response[3]

	if result_code != HTTPRequest.RESULT_SUCCESS or status_code < 200 or status_code >= 300:
		emit_signal("request_log", "API request failed for %s (%s/%s)." % [path, result_code, status_code])
		return {}

	var parsed = JSON.parse_string(body_bytes.get_string_from_utf8())
	if parsed is Dictionary:
		return parsed

	emit_signal("request_log", "API payload for %s is not a JSON object." % path)
	return {}

func _build_url(path: String) -> String:
	var normalized_base := base_url.rstrip("/")
	var normalized_path := path if path.begins_with("/") else "/%s" % path
	return "%s%s" % [normalized_base, normalized_path]
