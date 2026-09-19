<?php
/** @var string $selectedCity */
/** @var string|null $fieldId */
/** @var string|null $fieldName */
/** @var string|null $selectAttributes */
/** @var bool|null $required */

use App\Helpers\DestinationHelper;

$fieldId = $fieldId ?? 'host-city';
$fieldName = $fieldName ?? 'city';
$selectAttributes = $selectAttributes ?? '';
$required = $required ?? true;
$cities = DestinationHelper::priorityOrder();

if ($selectedCity !== '' && !in_array($selectedCity, $cities, true)) {
    array_unshift($cities, $selectedCity);
}
?>
<select name="<?= e($fieldName) ?>"
        id="<?= e($fieldId) ?>"
        <?= $required ? 'required' : '' ?>
        <?= $selectAttributes ?>>
    <?php foreach ($cities as $city): ?>
        <option value="<?= e($city) ?>"<?= $selectedCity === $city ? ' selected' : '' ?>><?= e($city) ?></option>
    <?php endforeach; ?>
</select>
