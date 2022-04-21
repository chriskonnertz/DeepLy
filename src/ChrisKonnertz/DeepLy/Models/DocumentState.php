<?php

namespace ChrisKonnertz\DeepLy\Models;

class DocumentState extends Model
{

    /**
     * The unique identifier of the document
     * @var string
     */
    public string $documentId = '';

    /**
     * The status of the document (queued, translating or done)
     *
     * @var string
     */
    public string $status = '';

    /**
     * How many characters were billed for this document?
     *
     * @var int
     */
    public int $billedCharacters = 0;

    /**
     * @var int|null
     */
    public int|null $secondsRemaining = null;

}