<?php

namespace eZ\Publish\Core\FieldType\Author;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy;

class AuthorThumbnailStrategy implements ThumbnailStrategy
{
    public function getThumbnail(Field $field): string
    {
        return sprintf('https://api.adorable.io/avatars/100/%s.io.png', (string)$field->value);
    }
}