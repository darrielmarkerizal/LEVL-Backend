# User Include Functionality Documentation

## Overview
The `/users` and `/users/:id` endpoints now support the `include` query parameter to load related data in a single request. This follows the JSON API specification for including related resources.

## Allowed Includes

The following relationships can be included:

### Auth Module
- `privacy_settings` - User's privacy settings
- `enrollments` - User's course enrollments
- `managed_courses` - Courses managed by the user (for admins/instructors)
- `received_overrides` - Learning overrides received by the user
- `granted_overrides` - Learning overrides granted by the user
- `roles` - User's assigned roles
- `media` - User's media files (avatar, etc.)
- `activities` - User's activity logs

### Gamification Module
- `gamification_stats` - User's gamification statistics (points, level, etc.)
- `badges` - Badges earned by the user
- `challenges` - Challenge assignments for the user
- `points` - Point transactions
- `levels` - Level progress
- `milestones` - Milestone achievements
- `learning_streaks` - Learning streak records

### Learning Module
- `submissions` - Assignment submissions
- `assignments` - Assignments created by the user

### Forums Module
- `posts` - Forum posts created by the user
- `threads` - Forum threads created by the user

## Usage Examples

### Single Include
```bash
GET /api/v1/users?include=privacy_settings
```

### Multiple Includes (comma-separated)
```bash
GET /api/v1/users?include=privacy_settings,gamification_stats,enrollments
```

### Include with Single User
```bash
GET /api/v1/users/123?include=badges,challenges,submissions
```

### Include with Pagination
```bash
GET /api/v1/users?include=enrollments,managed_courses&per_page=10&page=2
```

### Include with Filters
```bash
GET /api/v1/users?include=gamification_stats&status=active&role=Student
```

## Response Format

When includes are specified, the response will contain the requested relationships:

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "avatar_url": "https://...",
    "status": "active",
    "account_status": "active",
    "created_at": "2024-01-01T00:00:00+00:00",
    "email_verified_at": "2024-01-01T00:00:00+00:00",
    "roles": [
      {
        "id": 3,
        "name": "Student",
        "guard_name": "api"
      }
    ],
    "privacy_settings": {
      "id": 1,
      "user_id": 1,
      "profile_visibility": "public",
      "show_email": false,
      "show_phone": false,
      "show_activity_history": true,
      "show_achievements": true,
      "show_statistics": true
    },
    "gamification_stats": {
      "id": 1,
      "user_id": 1,
      "total_points": 1500,
      "current_level": 5,
      "current_streak": 7
    },
    "enrollments": [
      {
        "id": 1,
        "user_id": 1,
        "course_id": 1,
        "status": "active",
        "enrolled_at": "2024-01-01T00:00:00+00:00"
      }
    ]
  }
}
```

## Error Handling

### Invalid Include
If you request an include that is not in the allowed list, you will receive a validation error:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "include": [
      "Include 'invalid_relation' is not allowed. Allowed includes: privacy_settings, gamification_stats, enrollments, managed_courses, received_overrides, granted_overrides, badges, challenges, points, levels, milestones, learning_streaks, submissions, assignments, grades, posts, threads, activities, media, roles"
    ]
  }
}
```

## Implementation Details

### Request Validation
- `UserIncludeRequest` class validates the `include` parameter
- Only includes in the allowed list are accepted
- Multiple includes are separated by commas
- Whitespace around includes is trimmed

### Controller Logic
- `UserManagementController::index()` - Handles includes for user list
- `UserManagementController::show()` - Handles includes for single user
- Uses Laravel's `load()` method for eager loading relationships

### Model Relationships
All relationships are defined in the `User` model:
- `Modules/Auth/Models/User.php`

### Performance Considerations
- Eager loading prevents N+1 query problems
- Only requested relationships are loaded
- Pagination is maintained with includes

## Security

- Include parameter is validated against a whitelist
- Only authenticated users with Admin/Superadmin roles can access these endpoints
- Authorization policies are still enforced
- No sensitive data is exposed through includes

## Testing

### Test Cases to Implement

1. **Valid Single Include**
   - Request with valid single include
   - Assert relationship is loaded in response

2. **Valid Multiple Includes**
   - Request with multiple valid includes
   - Assert all relationships are loaded

3. **Invalid Include**
   - Request with invalid include
   - Assert 422 validation error
   - Assert error message lists allowed includes

4. **No Include**
   - Request without include parameter
   - Assert response contains only user data

5. **Include with Pagination**
   - Request with include and pagination
   - Assert both work together

6. **Include with Filters**
   - Request with include and filters
   - Assert both work together

7. **Include with Single User**
   - Request single user with include
   - Assert relationship is loaded

8. **Whitespace Handling**
   - Request with includes containing whitespace
   - Assert whitespace is trimmed correctly

## Future Enhancements

1. **Nested Includes**
   - Support for nested relationships (e.g., `enrollments.course`)
   - Example: `?include=enrollments.course,submissions.assignment`

2. **Sparse Fieldsets**
   - Support for selecting specific fields from included resources
   - Example: `?include=badges&fields[badges]=name,description`

3. **Include Limits**
   - Add configuration for maximum number of includes per request
   - Prevent performance issues with too many includes

4. **Include Metadata**
   - Add metadata about which includes are available
   - Document include relationships and their types

## API Documentation Update

Update your API documentation (Swagger/OpenAPI) to include:

```yaml
parameters:
  - name: include
    in: query
    description: Comma-separated list of relationships to include
    required: false
    schema:
      type: string
      example: "privacy_settings,gamification_stats,enrollments"
    enum:
      - privacy_settings
      - gamification_stats
      - enrollments
      - managed_courses
      - received_overrides
      - granted_overrides
      - badges
      - challenges
      - points
      - levels
      - milestones
      - learning_streaks
      - submissions
      - assignments
      - grades
      - posts
      - threads
      - activities
      - media
      - roles
```

## Migration Guide

If you have existing API clients:

1. **No Breaking Changes**
   - The `include` parameter is optional
   - Existing requests without `include` work as before

2. **Gradual Adoption**
   - Clients can start using includes incrementally
   - No need to update all clients at once

3. **Performance Benefits**
   - Encourage clients to use includes to reduce API calls
   - Document the performance improvements

## Support

For issues or questions about the include functionality:
- Check the allowed includes list in `UserIncludeRequest`
- Verify relationships are defined in the `User` model
- Review the controller implementation in `UserManagementController`
- Check API logs for validation errors