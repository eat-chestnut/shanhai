extends ScrollContainer
class_name MainlinePage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal start_battle

const NODE_MAP_SCENE := preload("res://scenes/components/node_map.tscn")
const DIFFICULTY_CARD_SCENE := preload("res://scenes/components/difficulty_card.tscn")

var _content: VBoxContainer
var _chapter_row: HBoxContainer
var _node_map
var _difficulty_box: VBoxContainer
var _start_button: Button
var _summary_label: Label

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	PlayerState.changed.connect(refresh)
	UiState.selection_changed.connect(_on_selection_changed)
	refresh()

func activate() -> void:
	call_deferred("_refresh_runtime_selection")

func refresh() -> void:
	if _content == null:
		return

	_sync_defaults()
	_rebuild_chapters()
	_rebuild_nodes()
	_rebuild_difficulties()
	_update_summary()

func _sync_defaults() -> void:
	if GameData.chapters.is_empty():
		return
	if str(UiState.selection.get("chapter_id", "")).is_empty():
		var current_chapter := GameData.chapters.filter(func(entry: Dictionary) -> bool: return bool(entry.get("is_current", false)))
		UiState.set_selection("chapter_id", str((current_chapter[0] if not current_chapter.is_empty() else GameData.chapters[0]).get("chapter_id", "")))
	var chapter := GameData.get_chapter(str(UiState.selection.get("chapter_id", "")))
	var nodes: Array = chapter.get("nodes", [])
	if nodes.is_empty():
		return
	if str(UiState.selection.get("node_id", "")).is_empty():
		var current_nodes := nodes.filter(func(entry: Dictionary) -> bool: return str(entry.get("progress_state", "")) == "current")
		UiState.set_selection("node_id", str((current_nodes[0] if not current_nodes.is_empty() else nodes[0]).get("node_id", "")))
	var node := GameData.get_mainline_node(str(UiState.selection.get("node_id", "")))
	var difficulties: Array = node.get("difficulties", [])
	if difficulties.is_empty():
		return
	if str(UiState.selection.get("difficulty_id", "")).is_empty():
		UiState.set_selection("difficulty_id", str(difficulties[0].get("difficulty_id", "")))

func _rebuild_chapters() -> void:
	for child in _chapter_row.get_children():
		child.queue_free()

	for chapter in GameData.chapters:
		var button := Button.new()
		var chapter_id := str(chapter.get("chapter_id", ""))
		button.text = "%s\n解锁 Lv.%d" % [chapter.get("chapter_name", "章节"), int(chapter.get("unlock_level", 1))]
		button.disabled = not bool(chapter.get("is_unlocked", PlayerState.get_level() >= int(chapter.get("unlock_level", 1))))
		ShanhaiStyle.apply_button(button, chapter_id == str(UiState.selection.get("chapter_id", "")))
		button.pressed.connect(_on_chapter_pressed.bind(chapter_id))
		_chapter_row.add_child(button)

func _rebuild_nodes() -> void:
	var chapter := GameData.get_chapter(str(UiState.selection.get("chapter_id", "")))
	_node_map.configure(chapter.get("nodes", []), str(UiState.selection.get("node_id", "")))

func _rebuild_difficulties() -> void:
	for child in _difficulty_box.get_children():
		child.queue_free()

	var node := GameData.get_mainline_node(str(UiState.selection.get("node_id", "")))
	for difficulty in node.get("difficulties", []):
		var difficulty_id := str(difficulty.get("difficulty_id", ""))
		var card = DIFFICULTY_CARD_SCENE.instantiate()
		card.configure(
			difficulty,
			difficulty_id == str(UiState.selection.get("difficulty_id", "")),
			bool(difficulty.get("is_unlocked", PlayerState.get_level() >= int(node.get("unlock_condition", {}).get("level", 1)))),
			PlayerState.get_power()
		)
		card.pressed.connect(_on_difficulty_pressed.bind(difficulty_id))
		_difficulty_box.add_child(card)

func _update_summary() -> void:
	var chapter := GameData.get_chapter(str(UiState.selection.get("chapter_id", "")))
	var node := GameData.get_mainline_node(str(UiState.selection.get("node_id", "")))
	var difficulty := GameData.get_difficulty_for_node(str(UiState.selection.get("node_id", "")), str(UiState.selection.get("difficulty_id", "")))
	var recommended_power := int(difficulty.get("recommended_power", 0))
	_summary_label.text = "当前章节：%s\n节点：%s  ·  状态：%s\n建议战力：%d  当前战力：%d\n难度状态：%s\n首通奖励：%s" % [
		chapter.get("chapter_name", "未选章节"),
		node.get("node_name", "未选节点"),
		_node_state_text(str(node.get("progress_state", "available"))),
		recommended_power,
		PlayerState.get_power(),
		_node_state_text(str(difficulty.get("progress_state", "available"))),
		_reward_preview(str(difficulty.get("first_clear_reward_group_id", "")))
	]
	_start_button.disabled = node.is_empty() or difficulty.is_empty() or not bool(node.get("is_unlocked", true)) or not bool(difficulty.get("is_unlocked", true))

func _on_node_selected(node: Dictionary) -> void:
	UiState.set_selection("node_id", str(node.get("node_id", "")))
	UiState.set_selection("difficulty_id", "")

func _on_chapter_pressed(chapter_id: String) -> void:
	UiState.set_selection("chapter_id", chapter_id)
	UiState.set_selection("node_id", "")
	UiState.set_selection("difficulty_id", "")

func _on_difficulty_pressed(difficulty_id: String) -> void:
	UiState.set_selection("difficulty_id", difficulty_id)

func _on_selection_changed() -> void:
	refresh()
	call_deferred("_refresh_runtime_selection")

func _refresh_runtime_selection() -> void:
	var node_id := str(UiState.selection.get("node_id", ""))
	if node_id.is_empty():
		return
	await GameData.load_stage_runtime_for_selection(node_id)

func _reward_preview(group_id: String) -> String:
	var rewards := GameData.get_reward_group_items(group_id)
	if rewards.is_empty():
		return "暂无"
	var labels: Array = []
	for reward in rewards:
		var definition := GameData.get_item_definition(str(reward.get("item_id", "")))
		labels.append("%s x%d" % [definition.get("name", reward.get("item_id", "奖励")), int(reward.get("count", 0))])
	return " / ".join(labels)

func _on_start_pressed() -> void:
	var chapter := GameData.get_chapter(str(UiState.selection.get("chapter_id", "")))
	var node := GameData.get_mainline_node(str(UiState.selection.get("node_id", "")))
	var difficulty := GameData.get_difficulty_for_node(str(UiState.selection.get("node_id", "")), str(UiState.selection.get("difficulty_id", "")))
	if chapter.is_empty() or node.is_empty() or difficulty.is_empty():
		return
	BattleState.start_mainline(chapter, node, difficulty)
	emit_signal("start_battle")

func _node_state_text(progress_state: String) -> String:
	match progress_state:
		"cleared":
			return "已通关"
		"current":
			return "当前推进"
		"available":
			return "已解锁"
		_:
			return "未解锁"

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

	var heading := Label.new()
	heading.text = "主线章节"
	ShanhaiStyle.apply_title(heading, 32)
	_content.add_child(heading)

	_chapter_row = HBoxContainer.new()
	_chapter_row.add_theme_constant_override("separation", 10)
	_content.add_child(_chapter_row)

	_node_map = NODE_MAP_SCENE.instantiate()
	_node_map.node_selected.connect(_on_node_selected)
	_content.add_child(_node_map)

	var difficulty_panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(difficulty_panel)
	_content.add_child(difficulty_panel)

	var difficulty_content := VBoxContainer.new()
	difficulty_content.add_theme_constant_override("separation", 12)
	difficulty_panel.add_child(difficulty_content)

	var difficulty_heading := Label.new()
	difficulty_heading.text = "难度选择"
	ShanhaiStyle.apply_heading(difficulty_heading, 22)
	difficulty_content.add_child(difficulty_heading)

	_difficulty_box = VBoxContainer.new()
	_difficulty_box.add_theme_constant_override("separation", 10)
	difficulty_content.add_child(_difficulty_box)

	_summary_label = Label.new()
	_summary_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_summary_label, false, 18)
	_content.add_child(_summary_label)

	_start_button = Button.new()
	_start_button.text = "进入巡厄战斗"
	ShanhaiStyle.apply_button(_start_button, true)
	_start_button.pressed.connect(_on_start_pressed)
	_content.add_child(_start_button)
