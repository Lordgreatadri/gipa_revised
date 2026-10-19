<?php

namespace App\Modules\Shared\Domain\Models;

use App\Modules\Shared\Domain\Concerns\HasUuidRouteKey;
use Illuminate\Database\Eloquent\Model;

/**
 * Reference data shared across contexts. Seeded from
 * database/seeders/CountryTableSeeder.php; not user-editable in Slice 1.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $code International dialling code, e.g. "+233"
 * @property string|null $currency
 * @property string|null $currency_symbol
 * @property string|null $country_currency
 * @property string $abbreviation ISO 3166-1 alpha-2
 * @property string|null $icon_url
 */
class Country extends Model
{
    use HasUuidRouteKey;

    protected $fillable = [
        'name',
        'code',
        'currency',
        'currency_symbol',
        'country_currency',
        'abbreviation',
        'icon_url',
    ];
}
