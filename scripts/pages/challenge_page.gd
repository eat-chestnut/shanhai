extends ScrollContainer
class_name ChallengePage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal start_battle

var _content: VBoxContainer
var _status_label: Label
var _challenge_list: VBoxContainer
var _detail_box: VBoxContainer
var _selected_challenge_id := ""

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	refresh()

func activate() -> void:
	call_deferred("_load_challenges")

func refresh() -> void:
	if _content == null:
		return

	_status_label.text = GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else "长线挑战每周重置周奖励状态，首次通关奖励永久保留。"

	for child in _challenge_list.get_children():
		child.queue_free()
	for child in _detail_box.get_children():
		child.queue_free()

	var entries := GameData.get_challenge_entries()
	if entries.is_empty():
		var empty := Label.new()
		empty.text = "当前暂无长线挑战。"
		ShanhaiStyle.apply_body(empty, true, 18)
		_detail_box.add_child(empty)
		return

	if _selected_challenge_id.is_empty():
		_selected_challenge_id = str(entries[0].get("challenge_id", ""))

	for entry in entries:
		_challenge_list.add_child(_build_challenge_button(entry))

	var detail := GameData.get_challenge_detail(_selected_challenge_id)
	var challenge: Dictionary = detail.get("challenge", {})
	if challenge.is_empty():
		return

	var summary := Label.new()
	summary.text = "%s\n当前周最高层 %d  /  历史最高层 %d\n推荐推进至 %d 层" % [
		str(challenge.get("challenge_desc", "长线挑战")),
		int(challenge.get("weekly_highest_floor", 0)),
		int(challenge.get("highest_floor", 0)),
		int(challenge.get("current_floor", 1))
	]
	summary.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(summary, false, 18)
	_detail_box.add_child(summary)

	for floor in challenge.get("floors", []):
		_detail_box.add_child(_build_floor_card(challenge, floor))

func _build_challenge_button(entry: Dictionary) -> Control:
	var button := Button.new()
	var challenge_id := str(entry.get("challenge_id", ""))
	button.text = "%s  ·  %s" % [
		str(entry.get("challenge_name", challenge_id)),
		"已解锁" if bool(entry.get("is_unlocked", false)) else str(entry.get("unlock_text", "未解锁"))
	]
	button.disabled = not bool(entry.get("is_unlocked", false))
	ShanhaiStyle.apply_button(button, challenge_id == _selected_challenge_id and not button.disabled)
	button.pressed.connect(_on_select_challenge.bind(challenge_id))
	return button

func _build_floor_card(challenge: Dictionary, floor: Dictionary) -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel, bool(floor.get("is_recommended", false)))

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 8)
	panel.add_child(content)

	var title := Label.new()
	title.text = "%s  ·  推荐战力 %d" % [str(floor.get("floor_name", floor.get("floor_id", "层数"))), int(floor.get("recommended_power", 0))]
	ShanhaiStyle.apply_heading(title, 20)
	content.add_child(title)

	var desc := Label.new()
	desc.text = "首通：%s  /  周奖励：%s" % [
		"已领" if bool(floor.get("is_first_clear_claimed", false)) else "未领",
		"已领" if bool(floor.get("is_weekly_reward_claimed", false)) else "可得"
	]
	desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(desc, false, 16)
	content.add_child(desc)

	var reward_label := Label.new()
	reward_label.text = "奖励预览：%s" % _reward_text(floor.get("reward_preview", []))
	reward_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(reward_label, true, 16)
	content.add_child(reward_label)

	var button := Button.new()
	button.text = "发起挑战"
	button.disabled = not bool(floor.get("is_unlocked", false))
	ShanhaiStyle.apply_button(button, not button.disabled)
	button.pressed.connect(_on_start_floor.bind(challenge.duplicate(true), floor.duplicate(true)))
	content.add_child(button)

	return panel

func _reward_text(rewards: Array) -> String:
	if rewards.is_empty():
		return "暂无"
	var labels: Array = []
	for reward in rewards:
		var definition := GameData.get_item_definition(str(reward.get("item_id", "")))
		labels.append("%s x%d" % [definition.get("name", reward.get("item_id", "奖励")), int(reward.get("count", 0))])
	return " / ".join(labels)

func _on_select_challenge(challenge_id: String) -> void:
	_selected_challenge_id = challenge_id
	refresh()
	call_deferred("_load_challenge_detail", challenge_id)

func _on_start_floor(challenge: Dictionary, floor: Dictionary) -> void:
	BattleState.start_challenge(challenge, floor)
	emit_signal("start_battle")

func _load_challenges() -> void:
	await GameData.load_challenge_runtime()
	var entries := GameData.get_challenge_entries()
	if not entries.is_empty():
		if _selected_challenge_id.is_empty():
			_selected_challenge_id = str(entries[0].get("challenge_id", ""))
		await _load_challenge_detail(_selected_challenge_id)

func _load_challenge_detail(challenge_id: String) -> void:
	await GameData.load_challenge_detail(challenge_id)

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	var margin := MarginContainer.new()
	margin.add_theme_constant_override("margin_left", 28)
	margin.add_theme_constant_override("margin_top", 18)
	margin.add_theme_constant_override("margin_right", 28)
	margin.add_theme_constant_override("margin_bottom", 18)
	add_child(margin)

	_content = VBoxContainer.new()
	_content.add_theme_constant_override("separation", 18)
	margin.add_child(_content)

	var intro := PanelContainer.new()
	ShanhaiStyle.apply_panel(intro, true)
	_content.add_child(intro)

	var intro_box := VBoxContainer.new()
	intro_box.add_theme_constant_override("separation", 8)
	intro.add_child(intro_box)

	var title := Label.new()
	title.text = "玄渊试炼塔"
	ShanhaiStyle.apply_title(title, 30)
	intro_box.add_child(title)

	_status_label = Label.new()
	_status_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_status_label, false, 18)
	intro_box.add_child(_status_label)

	_challenge_list = VBoxContainer.new()
	_challenge_list.add_theme_constant_override("separation", 10)
	_content.add_child(_challenge_list)

	_detail_box = VBoxContainer.new()
	_detail_box.add_theme_constant_override("separation", 12)
	_content.add_child(_detail_box)
