#!/bin/bash

# Test script to create a YouTube lesson block
# Make sure to update the IDs and token

echo "=== Testing YouTube Lesson Block Creation ==="
echo ""

# Variables (update these)
COURSE_SLUG="your-course-slug"
UNIT_SLUG="your-unit-slug"
LESSON_SLUG="your-lesson-slug"
TOKEN="your-bearer-token"

# Test 1: Create YouTube block
echo "1. Creating YouTube block..."
curl -X POST "http://localhost:8000/api/courses/${COURSE_SLUG}/units/${UNIT_SLUG}/lessons/${LESSON_SLUG}/blocks" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "youtube",
    "external_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "order": 1
  }' | jq '.'

echo ""
echo "2. Creating Google Drive block..."
curl -X POST "http://localhost:8000/api/courses/${COURSE_SLUG}/units/${UNIT_SLUG}/lessons/${LESSON_SLUG}/blocks" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "drive",
    "external_url": "https://drive.google.com/file/d/1abc123xyz/view",
    "order": 2
  }' | jq '.'

echo ""
echo "3. Creating generic link block..."
curl -X POST "http://localhost:8000/api/courses/${COURSE_SLUG}/units/${UNIT_SLUG}/lessons/${LESSON_SLUG}/blocks" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "link",
    "external_url": "https://example.com/resource",
    "content": "Click here to view the resource",
    "order": 3
  }' | jq '.'

echo ""
echo "✅ Test completed!"
