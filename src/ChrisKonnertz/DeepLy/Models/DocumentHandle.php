<?php

namespace ChrisKonnertz\DeepLy\Models;

class DocumentHandle extends Model
{

    /**
     * The unique identifier of the document
     * @var string
     */
    public string $documentId = '';

    /**
     * The document encryption key that was sent to the client after uploading the document
     *
     * @var string
     */
    public string $documentKey = '';

}