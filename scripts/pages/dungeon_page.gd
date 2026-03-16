extends ScrollContainer
class_name DungeonPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal start_battle

const DIFFICULTY_CARD_SCENE := preload("res://scenes/components/difficulty_card.tscn")
const ITEM_SLOT_SCENE := preload("res://scenes/components/item_slot.tscn")

var _content: VBoxContainer
var _dungeon_row: HBoxContainer
var _difficulty_box: VBoxContainer
var _summary_label: Label
var _start_button: Button
var _main_reward_box: FlowContainer
var _first_clear_reward_box: FlowContainer

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	PlayerState.changed.connect(refresh)
	UiState.selection_changed.connect(_on_selection_changed)
	refresh()

func activate() -> void:
	call_deferred("_refresh_runtime_detail")

func refresh() -> void:
	if _content == null:
		return
	_sync_defaults()
	_rebuild_dungeons()
	_rebuild_difficulties()
	_update_summary()
	_rebuild_reward_previews()

func _sync_defaults() -> void:
	if GameData.dungeons.is_empty():
		return
	if str(UiState.selection.get("dungeon_id", "")).is_empty():
		UiState.set_selection("dungeon_id", str(GameData.dungeons[0].get("dungeon_id", "")))
	var difficulties := GameData.get_difficulties_for_dungeon(str(UiState.selection.get("dungeon_id", "")))
	if difficulties.is_empty():
		return
	if str(UiState.selection.get("difficulty_id", "")).is_empty():
		var dungeon := GameData.get_dungeon(str(UiState.selection.get("dungeon_id", "")))
		UiState.set_selection("difficulty_id", str(dungeon.get("current_tier", difficulties[0].get("difficulty_id", ""))))

func _rebuild_dungeons() -> void:
	for child in _dungeon_row.get_children():
		child.queue_free()

	for dungeon in GameData.dungeons:
		var button := Button.new()
		var dungeon_id := str(dungeon.get("dungeon_id", ""))
		button.text = "%s\n%s  剩余 %d/%d" % [
			dungeon.get("dungeon_name", "副本"),
			dungeon.get("unlock_text", "解锁 Lv.%d" % int(dungeon.get("unlock_level", 1))),
			max(int(dungeon.get("daily_limit", 0)) - int(dungeon.get("daily_count", 0)), 0),
			int(dungeon.get("daily_limit", 0))
		]
		button.disabled = not bool(dungeon.get("is_unlocked", PlayerState.get_level() >= int(dungeon.get("unlock_level", 1))))
		ShanhaiStyle.apply_button(button, dungeon_id == str(UiState.selection.get("dungeon_id", "")))
		button.pressed.connect(_on_dungeon_pressed.bind(dungeon_id))
		_dungeon_row.add_child(button)

func _rebuild_difficulties() -> void:
	for child in _difficulty_box.get_children():
		child.queue_free()

	var dungeon_id := str(UiState.selection.get("dungeon_id", ""))
	var dungeon := GameData.get_dungeon(dungeon_id)
	for difficulty in dungeon.get("difficulties", []):
		var difficulty_id := str(difficulty.get("difficulty_id", ""))
		var card = DIFFICULTY_CARD_SCENE.instantiate()
		card.configure(
			difficulty,
			difficulty_id == str(UiState.selection.get("difficulty_id", "")),
			bool(difficulty.get("is_unlocked", PlayerState.get_level() >= int(dungeon.get("unlock_level", 1)))),
			PlayerState.get_power()
		)
		card.pressed.connect(_on_dungeon_difficulty_pressed.bind(difficulty_id))
		_difficulty_box.add_child(card)

func _update_summary() -> void:
	var dungeon := GameData.get_dungeon(str(UiState.selection.get("dungeon_id", "")))
	var selected_difficulty := {}
	for difficulty in dungeon.get("difficulties", []):
		if str(difficulty.get("difficulty_id", "")) == str(UiState.selection.get("difficulty_id", "")):
			selected_difficulty = difficulty
			break
	_summary_label.text = "副本：%s\n说明：%s\n当前层级：%s\n建议战力：%d\n建议：%s\n剩余次数：%d / %d%s" % [
		dungeon.get("dungeon_name", "未选择"),
		dungeon.get("dungeon_desc", "暂无说明"),
		selected_difficulty.get("tier_label", dungeon.get("current_tier", "未定")),
		int(selected_difficulty.get("recommended_power", 0)),
		selected_difficulty.get("recommendation_text", dungeon.get("suggestion_text", "可尝试挑战")),
		max(int(dungeon.get("daily_limit", 0)) - int(dungeon.get("daily_count", 0)), 0),
		int(dungeon.get("daily_limit", 0)),
		"\n%s" % GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else ""
	]
	_start_button.disabled = dungeon.is_empty() \
		or selected_difficulty.is_empty() \
		or not bool(dungeon.get("is_unlocked", true)) \
		or not bool(selected_difficulty.get("is_unlocked", true)) \
		or int(dungeon.get("remaining_count", 1)) <= 0

func _rebuild_reward_previews() -> void:
	for child in _main_reward_box.get_children():
		child.queue_free()
	for child in _first_clear_reward_box.get_children():
		child.queue_free()

	var dungeon := GameData.get_dungeon(str(UiState.selection.get("dungeon_id", "")))
	var selected_difficulty := {}
	for difficulty in dungeon.get("difficulties", []):
		if str(difficulty.get("difficulty_id", "")) == str(UiState.selection.get("difficulty_id", "")):
			selected_difficulty = difficulty
			break

	_build_item_slots(_main_reward_box, _main_reward_entries(selected_difficulty.get("main_rewards", dungeon.get("main_rewards", []))), "当前副本暂无主要产出预览。")
	_build_item_slots(_first_clear_reward_box, GameData.get_reward_group_items(str(selected_difficulty.get("first_clear_reward_group_id", ""))), "当前难度暂无首通奖励。")

func _on_start_pressed() -> void:
	var dungeon := GameData.get_dungeon(str(UiState.selection.get("dungeon_id", "")))
	var selected_difficulty := {}
	for difficulty in dungeon.get("difficulties", []):
		if str(difficulty.get("difficulty_id", "")) == str(UiState.selection.get("difficulty_id", "")):
			selected_difficulty = difficulty
			break
	if dungeon.is_empty() or selected_difficulty.is_empty():
		return
	BattleState.start_dungeon(dungeon, selected_difficulty)
	emit_signal("start_battle")

func _on_dungeon_pressed(dungeon_id: String) -> void:
	UiState.set_selection("dungeon_id", dungeon_id)
	UiState.set_selection("difficulty_id", "")

func _on_dungeon_difficulty_pressed(difficulty_id: String) -> void:
	UiState.set_selection("difficulty_id", difficulty_id)

func _on_selection_changed() -> void:
	refresh()
	call_deferred("_refresh_runtime_detail")

func _refresh_runtime_detail() -> void:
	var dungeon_id := str(UiState.selection.get("dungeon_id", ""))
	if dungeon_id.is_empty():
		return
	await GameData.load_dungeon_runtime_detail(dungeon_id)

func _main_reward_entries(main_rewards: Array) -> Array:
	var rewards: Array = []
	for item_id in main_rewards:
		rewards.append({
			"item_id": str(item_id),
			"count": 1
		})
	return rewards

func _build_item_slots(container: FlowContainer, rewards: Array, empty_text: String) -> void:
	if rewards.is_empty():
		var empty := Label.new()
		empty.text = empty_text
		ShanhaiStyle.apply_body(empty, true, 16)
		container.add_child(empty)
		return

	for reward in rewards:
		var slot = ITEM_SLOT_SCENE.instantiate()
		slot.custom_minimum_size = Vector2(240, 88)
		slot.configure(reward, int(reward.get("count", 0)))
		container.add_child(slot)

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

	var title := Label.new()
	title.text = "宗门副本"
	ShanhaiStyle.apply_title(title, 32)
	_content.add_child(title)

	_dungeon_row = HBoxContainer.new()
	_dungeon_row.add_theme_constant_override("separation", 10)
	_content.add_child(_dungeon_row)

	var difficulty_panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(difficulty_panel)
	_content.add_child(difficulty_panel)

	var difficulty_content := VBoxContainer.new()
	difficulty_content.add_theme_constant_override("separation", 12)
	difficulty_panel.add_child(difficulty_content)

	var heading := Label.new()
	heading.text = "难度选择"
	ShanhaiStyle.apply_heading(heading, 22)
	difficulty_content.add_child(heading)

	_difficulty_box = VBoxContainer.new()
	_difficulty_box.add_theme_constant_override("separation", 10)
	difficulty_content.add_child(_difficulty_box)

	_summary_label = Label.new()
	_summary_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_summary_label, false, 18)
	_content.add_child(_summary_label)

	var main_reward_heading := Label.new()
	main_reward_heading.text = "主要产出"
	ShanhaiStyle.apply_heading(main_reward_heading, 20)
	_content.add_child(main_reward_heading)

	_main_reward_box = FlowContainer.new()
	_main_reward_box.add_theme_constant_override("h_separation", 10)
	_main_reward_box.add_theme_constant_override("v_separation", 10)
	_content.add_child(_main_reward_box)

	var first_clear_heading := Label.new()
	first_clear_heading.text = "首通奖励"
	ShanhaiStyle.apply_heading(first_clear_heading, 20)
	_content.add_child(first_clear_heading)

	_first_clear_reward_box = FlowContainer.new()
	_first_clear_reward_box.add_theme_constant_override("h_separation", 10)
	_first_clear_reward_box.add_theme_constant_override("v_separation", 10)
	_content.add_child(_first_clear_reward_box)

	_start_button = Button.new()
	_start_button.text = "进入副本战斗"
	ShanhaiStyle.apply_button(_start_button, true)
	_start_button.pressed.connect(_on_start_pressed)
	_content.add_child(_start_button)
