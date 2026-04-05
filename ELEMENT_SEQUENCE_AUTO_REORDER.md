# Element Sequence Auto-Reordering Implementation

## Overview
Implemented automatic sequence reordering for unit elements (lessons, quizzes, and assignments) when they are deleted or restored from trash.

## Behavior

### On Delete
When an element is deleted:
1. The element is soft-deleted and moved to trash
2. All elements with sequence > deleted_sequence are automatically decremented by 1
3. The original sequence is stored in trash metadata for restoration

Example:
- Before: Elements 1.1, 1.2, 1.3, 1.4, 1.5
- Delete 1.3
- After: Elements 1.1, 1.2, 1.3 (was 1.4), 1.4 (was 1.5)

### On Restore
When an element is restored from trash:
1. All elements with sequence >= original_sequence are incremented by 1
2. The restored element is placed back at its original sequence
3. The element's original status is also restored

Example:
- Current: Elements 1.1, 1.2, 1.3, 1.4
- Restore element with original sequence 1.3
- After: Elements 1.1, 1.2, 1.3 (restored), 1.4 (was 1.3), 1.5 (was 1.4)

## Files Modified

### Backend

1. **Levl-BE/Modules/Schemes/app/Services/Support/LessonOrderingProcessor.php**
   - Updated `delete()` method to reorder all element types (lessons, quizzes, assignments)

2. **Levl-BE/Modules/Learning/app/Services/QuizService.php**
   - Updated `delete()` method to reorder all element types after quiz deletion

3. **Levl-BE/Modules/Learning/app/Services/AssignmentService.php**
   - Updated `delete()` method to reorder all element types after assignment deletion

4. **Levl-BE/Modules/Trash/app/Services/TrashBinService.php**
   - Added `extractOrder()` method to capture original sequence
   - Updated `recordSoftDeleted()` to store original_order in metadata
   - Updated `buildBulkTrashRecord()` to include original_order
   - Added `restoreOriginalOrder()` method to restore sequence on restore
   - Added `hasOrderColumn()` helper method
   - Updated `afterRestored()` to call restoreOriginalOrder

### Frontend

5. **Levl-FE/components/dashboard/unit-kompetensi/unit-detail.tsx**
   - Moved "Add Elements" button from elements section to page header (next to title)
   - Button now appears in CardContainer's rightContent prop

## Technical Details

### Delete Logic
```php
// When deleting any element type
DB::transaction(function () use ($element) {
    $unitId = $element->unit_id;
    $deletedOrder = $element->order;
    
    $deleted = $this->repository->delete($element);
    
    if ($deleted) {
        // Reorder all element types
        Lesson::where('unit_id', $unitId)
            ->where('order', '>', $deletedOrder)
            ->decrement('order');
        
        Quiz::where('unit_id', $unitId)
            ->where('order', '>', $deletedOrder)
            ->decrement('order');
        
        Assignment::where('unit_id', $unitId)
            ->where('order', '>', $deletedOrder)
            ->decrement('order');
    }
    
    return $deleted;
});
```

### Restore Logic
```php
// When restoring from trash
DB::transaction(function () use ($model, $originalOrder, $unitId) {
    // Make space at original position
    Lesson::where('unit_id', $unitId)
        ->where('order', '>=', $originalOrder)
        ->where('id', '!=', $model->getKey())
        ->increment('order');
    
    Quiz::where('unit_id', $unitId)
        ->where('order', '>=', $originalOrder)
        ->where('id', '!=', $model->getKey())
        ->increment('order');
    
    Assignment::where('unit_id', $unitId)
        ->where('order', '>=', $originalOrder)
        ->where('id', '!=', $model->getKey())
        ->increment('order');
    
    // Restore to original position
    $model->forceFill(['order' => $originalOrder]);
    $model->saveQuietly();
});
```

## Testing

To test the implementation:

1. **Delete Test**:
   - Create a unit with multiple elements (e.g., 1.1 to 1.10)
   - Delete element 1.5
   - Verify that 1.6 becomes 1.5, 1.7 becomes 1.6, etc.

2. **Restore Test**:
   - From the above state, restore the deleted element
   - Verify it returns to position 1.5
   - Verify all subsequent elements shift back (1.5 becomes 1.6, etc.)

3. **Mixed Element Types**:
   - Create a unit with lessons, quizzes, and assignments
   - Delete a lesson in the middle
   - Verify quizzes and assignments after it are reordered
   - Restore and verify all types shift correctly

## Notes

- All operations are wrapped in database transactions for data integrity
- The reordering applies to ALL element types in the unit, not just the same type
- Original sequence is preserved in trash metadata for accurate restoration
- The implementation uses `forceFill()` and `saveQuietly()` to avoid triggering additional events
