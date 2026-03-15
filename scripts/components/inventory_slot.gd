extends "res://scripts/components/item_slot.gd"
class_name InventorySlot

func _ready() -> void:
	super._ready()
	custom_minimum_size = Vector2(0, 96)
