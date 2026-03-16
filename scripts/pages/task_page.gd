extends ScrollContainer
class_name TaskPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var _content: VBoxContainer
var _status_label: Label
var _claim_all_button: Button
var _task_box: VBoxContainer

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	refresh()

func activate() -> void:
	call_deferred("_load_tasks")

func refresh() -> void:
	if _content == null:
		return

	_status_label.text = GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else "主线任务与每日任务都会以后端正式状态为准。"
	_claim_all_button.disabled = not GameData.has_claimable_tasks()

	for child in _task_box.get_children():
		child.queue_free()

	var tasks := GameData.get_task_entries()
	if tasks.is_empty():
		var empty := Label.new()
		empty.text = "当前暂无任务。"
		ShanhaiStyle.apply_body(empty, true, 18)
		_task_box.add_child(empty)
		return

	for task in tasks:
		_task_box.add_child(_build_task_card(task))

func _build_task_card(task: Dictionary) -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 8)
	panel.add_child(content)

	var title := Label.new()
	title.text = "%s  [%s]" % [
		task.get("task_name", task.get("task_id", "任务")),
		"主线" if str(task.get("task_type", "")) == "mainline" else "每日"
	]
	ShanhaiStyle.apply_heading(title, 22)
	content.add_child(title)

	var desc := Label.new()
	desc.text = "%s\n进度 %d / %d" % [
		task.get("task_desc", "暂无说明"),
		int(task.get("progress", 0)),
		int(task.get("target", 1))
	]
	desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(desc, false, 18)
	content.add_child(desc)

	var reward_label := Label.new()
	reward_label.text = "奖励：%s" % _reward_text(task.get("rewards", []))
	reward_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(reward_label, true, 16)
	content.add_child(reward_label)

	var action := Button.new()
	action.text = "已领取" if bool(task.get("is_claimed", false)) else "领取奖励"
	action.disabled = bool(task.get("is_claimed", false)) or not bool(task.get("can_claim", false))
	ShanhaiStyle.apply_button(action, not action.disabled)
	action.pressed.connect(_on_claim_pressed.bind(str(task.get("task_id", ""))))
	content.add_child(action)

	return panel

func _reward_text(rewards: Array) -> String:
	if rewards.is_empty():
		return "暂无"
	var labels: Array = []
	for reward in rewards:
		var definition := GameData.get_item_definition(str(reward.get("item_id", "")))
		labels.append("%s x%d" % [definition.get("name", reward.get("item_id", "奖励")), int(reward.get("count", 0))])
	return " / ".join(labels)

func _on_claim_pressed(task_id: String) -> void:
	await GameData.claim_task(task_id)

func _on_claim_all_pressed() -> void:
	await GameData.claim_all_tasks()

func _load_tasks() -> void:
	await GameData.load_task_runtime()

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
	title.text = "宗门任务"
	ShanhaiStyle.apply_title(title, 30)
	intro_box.add_child(title)

	_status_label = Label.new()
	_status_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_status_label, false, 18)
	intro_box.add_child(_status_label)

	_claim_all_button = Button.new()
	_claim_all_button.text = "一键领取可领奖励"
	ShanhaiStyle.apply_button(_claim_all_button, true)
	_claim_all_button.pressed.connect(_on_claim_all_pressed)
	_content.add_child(_claim_all_button)

	_task_box = VBoxContainer.new()
	_task_box.add_theme_constant_override("separation", 12)
	_content.add_child(_task_box)
