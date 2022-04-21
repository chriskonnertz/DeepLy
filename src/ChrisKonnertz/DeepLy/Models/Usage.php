<?php

namespace ChrisKonnertz\DeepLy\Models;

class Usage extends Model
{

    /**
     * Characters translated so far in the current billing period
     *
     * @var int|null
     */
    public int|null $characterCount = null;

    /**
     * Current maximum number of characters that can be translated per billing period
     *
     * @var int|null
     */
    public int|null $characterLimit = null;

    /**
     * Usage (0-1 / null)
     *
     * @var float|null
     */
    public float|null $characterQuota = null;

    /**
     * Documents translated so far in the current billing period if unknown
     *
     * @var int|null
     */
    public int|null $documentCount = null;

    /**
     * Current maximum number of documents that can be translated per billing period
     *
     * @var int|null
     */
    public int|null $documentLimit = null;

    /**
     * Usage (0-1 / null)
     *
     * @var float|null
     */
    public float|null $documentQuota = null;

    /**
     * Docs translated by all users in the team so far in the billing period
     *
     * @var int|null
     */
    public int|null $teamDocumentCount = null;

    /**
     * Max number of docs that can be translated by the team per billing period
     *
     * @var int|null
     */
    public int|null $teamDocumentLimit = null;

    /**
     * Usage (0-1 / null)
     *
     * @var float|null
     */
    public float|null $teamDocumentQuota = null;

    /**
     * @inheritDoc
     */
    protected function enrich()
    {
        // Calculate percentages
        if ($this->characterLimit !== null) {
            $this->characterQuota = $this->characterCount / $this->characterLimit;
        }
        if ($this->documentLimit !== null) {
            $this->documentQuota = $this->documentCount / $this->documentLimit;
        }
        if ($this->teamDocumentLimit !== null) {
            $this->teamDocumentQuota = $this->teamDocumentCount / $this->teamDocumentLimit;
        }
    }

}