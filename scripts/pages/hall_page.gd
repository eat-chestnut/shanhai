extends ScrollContainer
class_name HallPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal navigate(screen: String)

var _content: VBoxContainer

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	PlayerState.changed.connect(refresh)
	refresh()

func refresh() -> void:
	if _content == null:
		return

	for child in _content.get_children():
		child.queue_free()

	_content.add_child(_build_banner())
	_content.add_child(_build_feature_section())
	_content.add_child(_build_quick_links())

func _build_banner() -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel, true)

	var box := VBoxContainer.new()
	box.add_theme_constant_override("separation", 10)
	panel.add_child(box)

	var title := Label.new()
	title.text = "山门烟云起，巡厄当此时"
	ShanhaiStyle.apply_title(title, 30)
	box.add_child(title)

	var stats := PlayerState.get_total_stats()
	var desc := Label.new()
	desc.text = "%s  Lv.%d  战力 %d\n宗门议事、巡山主线与副本入口都汇聚于此。" % [
		GameData.get_character_class_name(str(PlayerState.player.get("class_id", ""))),
		PlayerState.get_level(),
		int(stats.get("power", 0))
	]
	desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(desc, false, 18)
	box.add_child(desc)

	return panel

func _build_feature_section() -> Control:
	var section := VBoxContainer.new()
	section.add_theme_constant_override("separation", 14)

	var heading := Label.new()
	heading.text = "大厅功能"
	ShanhaiStyle.apply_heading(heading, 24)
	section.add_child(heading)

	for feature in GameData.hall_features:
		var feature_payload: Dictionary = feature.duplicate(true)
		var button := Button.new()
		var is_unlocked := PlayerState.is_feature_unlocked(feature)
		button.text = "%s  ·  %s" % [feature.get("feature_name", "功能"), "已解锁" if is_unlocked else "等级不足"]
		button.disabled = not is_unlocked
		ShanhaiStyle.apply_button(button, is_unlocked)
		button.pressed.connect(_handle_feature.bind(feature_payload))
		section.add_child(button)

	return section

func _build_quick_links() -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 10)
	panel.add_child(content)

	var heading := Label.new()
	heading.text = "当前巡厄目标"
	ShanhaiStyle.apply_heading(heading, 22)
	content.add_child(heading)

	for entry in [
		{"screen": UiState.SCREEN_MAINLINE, "label": "进入主线章节"},
		{"screen": UiState.SCREEN_SCRIPTURE_LIST, "label": "进入经卷回刷"},
		{"screen": UiState.SCREEN_DUNGEON, "label": "进入宗门副本"},
		{"screen": UiState.SCREEN_TASK, "label": "查看宗门任务"},
		{"screen": UiState.SCREEN_SHOP, "label": "进入宗门商店"},
		{"screen": UiState.SCREEN_IDLE, "label": "领取闭关收益"},
		{"screen": UiState.SCREEN_CHALLENGE, "label": "挑战玄渊试炼"},
		{"screen": UiState.SCREEN_INVENTORY, "label": "查看行囊与装备"}
	]:
		var screen := str(entry.get("screen", ""))
		var button := Button.new()
		button.text = str(entry.get("label", ""))
		ShanhaiStyle.apply_button(button)
		button.pressed.connect(_emit_navigation.bind(screen))
		content.add_child(button)

	return panel

func _handle_feature(feature: Dictionary) -> void:
	var page := str(feature.get("jump_target", {}).get("page", ""))
	match page:
		"trial":
			emit_signal("navigate", UiState.SCREEN_TASK)
		"shop":
			emit_signal("navigate", UiState.SCREEN_SHOP)
		"mainline":
			emit_signal("navigate", UiState.SCREEN_MAINLINE)
		"dungeon":
			emit_signal("navigate", UiState.SCREEN_DUNGEON)
		"idle":
			emit_signal("navigate", UiState.SCREEN_IDLE)
		"challenge":
			emit_signal("navigate", UiState.SCREEN_CHALLENGE)
		_:
			pass

func _emit_navigation(screen: String) -> void:
	emit_signal("navigate", screen)

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
