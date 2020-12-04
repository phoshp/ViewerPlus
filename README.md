# ViewerPlus

A api to make better spectator

#### API
```php
ViewerPlus::setViewer($player, ViewerPlus::VIEWER_SURVIVAL); // viewer in survival mode
ViewerPlus::setViewer($player, ViewerPlus::VIEWER_CREATIVE); // viewer in creative mode
ViewerPlus::setViewer($player, ViewerPlus::VIEWER_SURVIVAL_NO_CLIP); // survival + no clip
// removes viewer sets player gamemode to second arg(its survival mode as default)
ViewerPlus::removeViewer($player, Player::SURVIVAL); 
```