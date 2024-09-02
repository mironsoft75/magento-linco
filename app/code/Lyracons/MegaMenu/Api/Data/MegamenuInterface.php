<?php
namespace Lyracons\MegaMenu\Api\Data;

interface MegamenuInterface
{

    public const MENU_ID = 'menu_id';
    public const IDENTIFIER = 'identifier';
    public const TITLE = 'title';
    public const CONTENT = 'content';
    public const IS_ACTIVE = 'is_active';
    public const CSS_CLASS = 'css_class';
    public const TYPE = 'type';

    public function getMenuId();

    public function getTitle();

    public function getIdentifier();

    public function getContent();

    public function getIsActive();

    public function getCssClass();

    public function getType();

    public function setMenuId($menuId);

    public function setTitle($title);

    public function setIdentifier($identifier);

    public function setContent($content);

    public function setIsActive($isActive);

    public function setCssClass($cssClass);

    public function setType($type);
}
