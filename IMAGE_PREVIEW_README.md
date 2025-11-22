# Image Preview Feature

This document describes the image preview and upload functionality added to the Student Result Management System.

## Features

### 1. Image Preview
- Real-time preview of selected images before upload
- Visual feedback with placeholder when no image is selected
- Click to view full-size image in new tab
- Remove button to clear selected image

### 2. Image Upload
- Supports JPEG, JPG, PNG, GIF, and WebP formats
- Maximum file size: 5MB
- Automatic validation of file type and size
- Unique filename generation to prevent conflicts
- Secure storage in `images/uploads/` directory

### 3. Profile Pictures
- Optional profile picture upload during student registration
- Profile pictures displayed in:
  - Student dashboard
  - Admin student management page
  - Student profile views

## Usage

### For Students

1. **During Signup:**
   - When registering, you can optionally upload a profile picture
   - Click "Choose File" and select an image
   - Preview will appear automatically
   - Click the preview image to view full size
   - Click "Remove" to clear and select a different image

2. **Viewing Profile:**
   - Your profile picture appears in your dashboard
   - If no picture is uploaded, a default avatar icon is shown

### For Administrators

1. **Registering Students:**
   - When adding a new student, you can upload their profile picture
   - Same preview functionality as student signup

2. **Viewing Students:**
   - All student profile pictures appear in the student management table
   - Click on any profile picture to view full size

## Technical Details

### Files Added

- `js/image-preview.js` - Reusable JavaScript module for image preview
- `upload_image.php` - Image upload handler (for future AJAX uploads)
- `images/uploads/` - Directory for storing uploaded images

### Files Modified

- `student_signup.php` - Added image upload field with preview
- `add_students.php` - Added image upload field with preview
- `student_dashboard.php` - Added profile picture display
- `manage_students.php` - Added profile picture column in table

### Database Changes

The system automatically adds a `profile_picture` column to the `student_records` table:
- Column: `profile_picture VARCHAR(255) NULL`
- Stores relative path to uploaded image (e.g., `images/uploads/profile_xxx.jpg`)

### Image Validation

- **Allowed Types:** JPEG, JPG, PNG, GIF, WebP
- **Maximum Size:** 5MB
- **Storage:** `images/uploads/` directory
- **Naming:** Unique filenames with timestamp to prevent conflicts

## Security

- File type validation on both client and server side
- File size limits enforced
- Secure file storage outside web root (relative to project)
- Unique filenames prevent overwriting existing files
- Only authenticated users can upload images

## Browser Compatibility

- Chrome (Recommended)
- Firefox
- Safari
- Edge
- Opera

## Troubleshooting

### Image Preview Not Showing
- Ensure `js/image-preview.js` is loaded before inline scripts
- Check browser console for JavaScript errors
- Verify image file type is supported

### Upload Failing
- Check file size (must be under 5MB)
- Verify file type (JPEG, PNG, GIF, or WebP)
- Ensure `images/uploads/` directory exists and is writable
- Check server permissions on upload directory

### Profile Picture Not Displaying
- Verify image file exists in `images/uploads/` directory
- Check database record has correct path stored
- Ensure image path in database is relative (e.g., `images/uploads/filename.jpg`)

## Future Enhancements

- Image cropping before upload
- Multiple image uploads
- Image compression on upload
- Profile picture editing in dashboard
- Bulk image upload for admins

