extends RefCounted
class_name ShanhaiStyle

const BG := Color("161310")
const PANEL := Color("2b221c")
const PANEL_ALT := Color("3c3026")
const PANEL_SOFT := Color("4c3e31")
const INK := Color("f5ead7")
const MUTED := Color("cdbca3")
const ACCENT := Color("d88d3d")
const ACCENT_SOFT := Color("f0c77a")
const SUCCESS := Color("79c18d")
const DANGER := Color("d46855")
const BOSS := Color("bf5142")

static func make_panel(background: Color = PANEL, border: Color = ACCENT_SOFT, radius: int = 20, border_width: int = 2) -> StyleBoxFlat:
	var style := StyleBoxFlat.new()
	style.bg_color = background
	style.border_color = border
	style.border_width_left = border_width
	style.border_width_top = border_width
	style.border_width_right = border_width
	style.border_width_bottom = border_width
	style.corner_radius_top_left = radius
	style.corner_radius_top_right = radius
	style.corner_radius_bottom_right = radius
	style.corner_radius_bottom_left = radius
	style.content_margin_left = 16
	style.content_margin_top = 14
	style.content_margin_right = 16
	style.content_margin_bottom = 14
	return style

static func make_button_state(background: Color, border: Color, radius: int = 18) -> StyleBoxFlat:
	return make_panel(background, border, radius, 2)

static func apply_button(button: BaseButton, emphasized: bool = false) -> void:
	button.add_theme_stylebox_override("normal", make_button_state(PANEL_ALT if emphasized else PANEL, ACCENT_SOFT if emphasized else Color("8f6c49")))
	button.add_theme_stylebox_override("hover", make_button_state(PANEL_SOFT, ACCENT))
	button.add_theme_stylebox_override("pressed", make_button_state(ACCENT, ACCENT_SOFT))
	button.add_theme_stylebox_override("disabled", make_button_state(Color("302820"), Color("5d4b37")))
	button.add_theme_color_override("font_color", INK)
	button.add_theme_color_override("font_hover_color", INK)
	button.add_theme_color_override("font_pressed_color", BG)
	button.add_theme_color_override("font_disabled_color", MUTED)
	button.add_theme_font_size_override("font_size", 20)
	button.custom_minimum_size = Vector2(0, 62)

static func apply_panel(panel: Control, alt: bool = false) -> void:
	panel.add_theme_stylebox_override("panel", make_panel(PANEL_ALT if alt else PANEL, ACCENT_SOFT if alt else Color("71573b")))

static func apply_title(label: Label, size: int = 34) -> void:
	label.add_theme_color_override("font_color", INK)
	label.add_theme_font_size_override("font_size", size)

static func apply_heading(label: Label, size: int = 24) -> void:
	label.add_theme_color_override("font_color", ACCENT_SOFT)
	label.add_theme_font_size_override("font_size", size)

static func apply_body(label: Label, muted: bool = false, size: int = 18) -> void:
	label.add_theme_color_override("font_color", MUTED if muted else INK)
	label.add_theme_font_size_override("font_size", size)

static func apply_line(line: ColorRect) -> void:
	line.color = Color("7e6547")
	line.custom_minimum_size = Vector2(0, 2)

