extends ScrollContainer
class_name ScriptureListPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal open_detail(scripture_id: String)

var _content: VBoxContainer
var _status_label: Label
var _list_box: VBoxContainer

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	UiState.selection_changed.connect(refresh)
	refresh()

func activate() -> void:
	call_deferred("_load_scriptures")

func refresh() -> void:
	if _content == null:
		return

	_status_label.text = GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else "经卷状态以正式运行态接口为准。"

	for child in _list_box.get_children():
		child.queue_free()

	var entries := GameData.get_scripture_entries()
	if entries.is_empty():
		var empty := Label.new()
		empty.text = "当前暂无经卷配置。"
		ShanhaiStyle.apply_body(empty, true, 18)
		_list_box.add_child(empty)
		return

	for scripture in entries:
		_list_box.add_child(_build_scripture_card(scripture))

func _build_scripture_card(scripture: Dictionary) -> Control:
	var scripture_id := str(scripture.get("scripture_id", ""))
	var selected := scripture_id == str(UiState.selection.get("scripture_id", ""))
	var is_unlocked := bool(scripture.get("is_unlocked", false))

	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel, selected or is_unlocked)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 10)
	panel.add_child(content)

	var title := Label.new()
	title.text = "%s  ·  %s" % [
		scripture.get("scripture_name", scripture_id),
		scripture.get("scripture_group", "")
	]
	ShanhaiStyle.apply_heading(title, 24)
	content.add_child(title)

	var summary := Label.new()
	summary.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	summary.text = "%s\n当前强度：%d\n已解锁最高强度：%d\n%s" % [
		"已解锁" if is_unlocked else "未解锁",
		int(scripture.get("current_world_level", 0)),
		int(scripture.get("max_unlocked_world_level", 0)),
		str(scripture.get("unlock_text", ""))
	]
	ShanhaiStyle.apply_body(summary, false, 18)
	content.add_child(summary)

	var button := Button.new()
	button.text = "查看经卷"
	ShanhaiStyle.apply_button(button, selected or is_unlocked)
	button.pressed.connect(func() -> void:
		emit_signal("open_detail", scripture_id)
	)
	content.add_child(button)

	return panel

func _load_scriptures() -> void:
	await GameData.load_scripture_runtime()

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
	title.text = "经卷回刷"
	ShanhaiStyle.apply_title(title, 30)
	intro_box.add_child(title)

	_status_label = Label.new()
	_status_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_status_label, false, 18)
	intro_box.add_child(_status_label)

	_list_box = VBoxContainer.new()
	_list_box.add_theme_constant_override("separation", 12)
	_content.add_child(_list_box)
