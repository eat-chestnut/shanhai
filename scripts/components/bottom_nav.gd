extends PanelContainer
class_name BottomNav

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal navigate(screen: String)

var _buttons: Dictionary = {}
var _tabs := [
	{"screen": UiState.SCREEN_HALL, "label": "大厅"},
	{"screen": UiState.SCREEN_MAINLINE, "label": "主线"},
	{"screen": UiState.SCREEN_DUNGEON, "label": "副本"},
	{"screen": UiState.SCREEN_INVENTORY, "label": "行囊"}
]

func _ready() -> void:
	_build_ui()
	UiState.screen_changed.connect(_refresh_active_state)
	_refresh_active_state(UiState.current_screen)

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	ShanhaiStyle.apply_panel(self)
	size_flags_horizontal = Control.SIZE_EXPAND_FILL

	var row := HBoxContainer.new()
	row.alignment = BoxContainer.ALIGNMENT_CENTER
	row.add_theme_constant_override("separation", 12)
	add_child(row)

	for tab in _tabs:
		var button := Button.new()
		var screen := str(tab.get("screen", ""))
		button.text = str(tab.get("label", ""))
		button.size_flags_horizontal = Control.SIZE_EXPAND_FILL
		ShanhaiStyle.apply_button(button)
		button.pressed.connect(_on_tab_pressed.bind(screen))
		row.add_child(button)
		_buttons[screen] = button

func _refresh_active_state(active_screen: String) -> void:
	visible = UiState.should_show_navigation()
	for screen in _buttons.keys():
		var button: Button = _buttons[screen]
		ShanhaiStyle.apply_button(button, screen == active_screen)

func _on_tab_pressed(screen: String) -> void:
	emit_signal("navigate", screen)
