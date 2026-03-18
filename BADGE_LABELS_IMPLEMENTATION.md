# Badge Type & Rarity Labels Implementation

## Overview

Implementasi label translations untuk badge `type` dan `rarity` di API response, sehingga frontend tidak perlu melakukan mapping sendiri.

## Changes Made

### 1. Backend - BadgeResource

**File:** `Levl-BE/Modules/Gamification/app/Http/Resources/BadgeResource.php`

Added `type_label` and `rarity_label` fields yang menggunakan method `label()` dari enum:

```php
public function toArray(Request $request): array
{
    return [
        // ... other fields
        'type' => $this->type?->value,
        'type_label' => $this->type?->label(),  // ✅ Added
        'rarity' => $this->rarity?->value,
        'rarity_label' => $this->rarity?->label(),  // ✅ Added
        // ... other fields
    ];
}
```

### 2. Backend - Badge Model

**File:** `Levl-BE/Modules/Gamification/app/Models/Badge.php`

Updated `$appends` to include `icon_thumb_url`:

```php
protected $appends = ['icon_url', 'icon_thumb_url'];  // ✅ Added icon_thumb_url
```

### 3. Enum Translations

**Files:**
- `Levl-BE/lang/en/enums.php`
- `Levl-BE/lang/id/enums.php`

All badge types and rarities already have complete translations:

**Badge Types (7 types):**
| Value      | EN Label     | ID Label      |
|------------|--------------|---------------|
| completion | Completion   | Penyelesaian  |
| quality    | Quality      | Kualitas      |
| speed      | Speed        | Kecepatan     |
| habit      | Habit        | Kebiasaan     |
| social     | Social       | Sosial        |
| milestone  | Milestone    | Pencapaian    |
| hidden     | Hidden       | Tersembunyi   |

**Badge Rarities (5 rarities):**
| Value      | EN Label    | ID Label      |
|------------|-------------|---------------|
| common     | Common      | Umum          |
| uncommon   | Uncommon    | Tidak Umum    |
| rare       | Rare        | Langka        |
| epic       | Epic        | Epik          |
| legendary  | Legendary   | Legendaris    |

## API Response Example

### Before
```json
{
  "id": 111,
  "code": "night_owl",
  "name": "Kelelawar Malam",
  "type": "hidden",
  "rarity": "common"
}
```

### After
```json
{
  "id": 111,
  "code": "night_owl",
  "name": "Kelelawar Malam",
  "type": "hidden",
  "type_label": "Tersembunyi",
  "rarity": "common",
  "rarity_label": "Umum"
}
```

## Frontend Integration

### TypeScript Types

**File:** `Levl-FE/hooks/api/types/badges.ts`

```typescript
export interface Badge {
  id: number;
  code: string;
  name: string;
  type: BadgeType;
  type_label: string | null;  // ✅ Already defined
  rarity: BadgeRarity | null;
  rarity_label: string | null;  // ✅ Already defined
  // ... other fields
}
```

### Usage in Components

**File:** `Levl-FE/components/dashboard/lencana/badges-table.tsx`

```typescript
// Display type label with fallback
<span>{badge.type_label || badge.type || "-"}</span>

// Display rarity label with fallback
<span>{badge.rarity_label || badge.rarity || "Common"}</span>
```

## Benefits

1. ✅ **Centralized Translations** - All translations managed in backend
2. ✅ **Consistent Labels** - Same labels across all API consumers
3. ✅ **Easy Maintenance** - Update translations in one place
4. ✅ **Multi-language Support** - Automatic language switching based on Accept-Language header
5. ✅ **Type Safety** - Frontend still uses enums for type checking

## Testing

### Test Badge Labels

```bash
cd Levl-BE
php artisan tinker --execute="
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Http\Resources\BadgeResource;

\$badge = Badge::first();
\$resource = new BadgeResource(\$badge);
\$array = \$resource->toArray(request());

echo 'Type: ' . \$array['type'] . PHP_EOL;
echo 'Type Label: ' . \$array['type_label'] . PHP_EOL;
echo 'Rarity: ' . \$array['rarity'] . PHP_EOL;
echo 'Rarity Label: ' . \$array['rarity_label'] . PHP_EOL;
"
```

Expected output:
```
Type: milestone
Type Label: Pencapaian
Rarity: common
Rarity Label: Umum
```

### Test All Translations

```bash
php artisan tinker --execute="
use Modules\Gamification\Enums\BadgeType;
use Modules\Gamification\Enums\BadgeRarity;

echo 'Badge Types:' . PHP_EOL;
foreach (BadgeType::cases() as \$type) {
    echo '  ' . \$type->value . ' => ' . \$type->label() . PHP_EOL;
}

echo PHP_EOL . 'Badge Rarities:' . PHP_EOL;
foreach (BadgeRarity::cases() as \$rarity) {
    echo '  ' . \$rarity->value . ' => ' . \$rarity->label() . PHP_EOL;
}
"
```

### Test API Endpoint

```bash
# Get badges with labels
curl -X GET "http://localhost:8000/api/v1/badges?per_page=3" \
  -H "Accept: application/json" \
  -H "Accept-Language: id" \
  | jq '.data[] | {code, type, type_label, rarity, rarity_label}'
```

## Language Switching

The API automatically returns labels in the correct language based on the `Accept-Language` header:

```bash
# Indonesian labels
curl -H "Accept-Language: id" http://localhost:8000/api/v1/badges

# English labels
curl -H "Accept-Language: en" http://localhost:8000/api/v1/badges
```

## Related Files

### Backend
- `Levl-BE/Modules/Gamification/app/Http/Resources/BadgeResource.php` - Resource with labels
- `Levl-BE/Modules/Gamification/app/Models/Badge.php` - Model with enum casts
- `Levl-BE/Modules/Gamification/app/Enums/BadgeType.php` - Type enum with label()
- `Levl-BE/Modules/Gamification/app/Enums/BadgeRarity.php` - Rarity enum with label()
- `Levl-BE/lang/en/enums.php` - English translations
- `Levl-BE/lang/id/enums.php` - Indonesian translations

### Frontend
- `Levl-FE/hooks/api/types/badges.ts` - TypeScript types
- `Levl-FE/components/dashboard/lencana/badges-table.tsx` - Table component
- `Levl-FE/messages/en/page.json` - Frontend EN translations (for reference)
- `Levl-FE/messages/id/page.json` - Frontend ID translations (for reference)

## Migration History

1. ✅ Badge type enum updated from 3 to 7 values (2026_03_16_195614)
2. ✅ Badge rarity enum already exists with 5 values (2026_03_14_150000)
3. ✅ Type and rarity labels added to API response
4. ✅ All translations complete in EN and ID

## Notes

- Labels are generated dynamically based on the enum's `label()` method
- Translations use Laravel's `__()` helper for i18n support
- Frontend can still use enums for type checking and validation
- Labels are optional (nullable) to handle edge cases
- The `icon_thumb_url` is now always included in the response
