<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface ExportableItem
{
    /**
     * Whether this item was already exported.
     *
     * @return bool
     */
    public function isExported(): bool;

    /**
     * Whether this item should be included in invoices.
     *
     * @return bool
     */
    public function isBillable(): bool;

    /**
     * Returns the named meta field or null.
     *
     * @param string $name
     * @return MetaTableTypeInterface|null
     */
    public function getMetaField(string $name): ?MetaTableTypeInterface;

    /**
     * Returns all assigned tag names.
     *
     * @return string[]
     */
    public function getTagsAsArray(): array;

    /**
     * Returns the amount for this item.
     *
     * @return float
     */
    public function getAmount(): float;

    public function getActivity(): ?Activity;

    public function getProject(): ?Project;

    public function getFixedRate(): ?float;

    public function getHourlyRate(): ?float;

    public function getRate(): float;

    public function getInternalRate(): ?float;

    public function getUser(): ?User;

    public function getBegin(): ?\DateTime;

    public function getEnd(): ?\DateTime;

    public function getDuration(): ?int;

    public function getDescription(): ?string;

    /**
     * @return MetaTableTypeInterface[]
     */
    public function getVisibleMetaFields(): array;

    /**
     * @return Collection<MetaTableTypeInterface>
     */
    public function getMetaFields(): Collection;

    /**
     * A name representation for this type of item.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * A name representation for the category of this item.
     *
     * @return string
     */
    public function getCategory(): string;
}
