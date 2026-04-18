<?php

return [
    // Auth Emails
    'credentials' => [
        'subject' => 'Your Account Credentials',
        'title' => 'Your Account Has Been Created',
        'greeting' => 'Hello :name,',
        'body' => 'Your account has been created by the administrator. Use the following credentials to log in, then immediately change your password after logging in.',
        'email_label' => 'Email',
        'password_label' => 'Temporary Password',
        'button' => 'Login Now',
        'footer' => 'If you believe you are not related to this account creation, please ignore this email.',
    ],

    'verify' => [
        'subject' => 'Verify Your Email',
        'title' => 'Verify Your Email',
        'greeting' => 'Hello :name,',
        'body' => 'Thank you for registering. To complete your registration, please verify your email address by clicking the button below:',
        'button' => 'Verify My Email',
        'info' => '<strong>Important:</strong> This verification link is valid for :minutes minutes and can only be used once.',
        'footer' => 'If you did not create this account, you can safely ignore this email.',
    ],

    'reset' => [
        'subject' => 'Reset Your Password',
        'title' => 'Reset Your Password',
        'greeting' => 'Hello :name,',
        'body' => 'We received a request to reset your account password. To continue, click the button below:',
        'button' => 'Reset Password',
        'info' => '<strong>Important:</strong> This reset link is valid for :minutes minutes and can only be used once.',
        'footer' => 'If you did not request a password reset, you can safely ignore this email.',
    ],

    'change_email' => [
        'subject' => 'Verify Email Change',
        'title' => 'Verify Email Change',
        'greeting' => 'Hello :name,',
        'body' => 'We received a request to <strong>change your account email</strong> to <strong>:new_email</strong>.',
        'body_confirm' => 'If this is correct, please confirm the change by clicking the button below:',
        'button' => 'Verify Email Change',
        'info' => '<strong>Important:</strong> This link is valid for :minutes minutes and can only be used once.',
        'footer' => 'If you did not request this change, please ignore this email.',
    ],

    'delete_account' => [
        'subject' => 'Confirm Account Deletion',
        'title' => 'Confirm Account Deletion',
        'greeting' => 'Hello :name,',
        'body_1' => 'We received a request to permanently delete your account.',
        'body_2' => 'This action <strong>cannot be undone</strong>. All your data will be permanently deleted from our system.',
        'button' => 'Permanently Delete My Account',
        'info' => '<strong>Important:</strong> This confirmation link is valid for :minutes minutes and can only be used once.',
        'warning' => 'If you did not request account deletion, secure your account immediately as someone may have access to your password.',
        'footer' => 'This email was sent automatically by the security system.',
    ],

    'users_export' => [
        'subject' => 'User Data Export',
        'title' => 'User Data Export',
        'greeting' => 'Hello,',
        'body' => 'Your user data export has been completed and is attached to this email.',
        'file_label' => 'File',
        'footer' => 'This email was sent automatically. Please do not reply to this email.',
    ],

    // Enrollment Emails
    'enrollment_active' => [
        'subject' => 'Enrollment Successful',
        'title' => 'Congratulations! Enrollment Successful',
        'greeting' => 'Hello :name,',
        'body' => 'You have successfully enrolled in the following course:',
        'info' => '<strong>Status:</strong> You are now actively enrolled and can start accessing the course materials.',
        'button' => 'Access Course',
        'footer' => 'Happy learning!',
    ],

    'enrollment_pending' => [
        'subject' => 'Enrollment Request Submitted',
        'title' => 'Enrollment Request Submitted',
        'greeting' => 'Hello :name,',
        'body' => 'Thank you for your interest in enrolling in the following course:',
        'info' => '<strong>Status:</strong> Your enrollment request is pending approval from the course admin or instructor. You will receive an email notification once your request has been approved or declined.',
        'confirmation' => 'We will send you a confirmation email as soon as your request is processed.',
        'footer' => 'Thank you for your patience.',
    ],

    'enrollment_manual_active' => [
        'subject' => 'You Have Been Enrolled in a Course',
        'title' => 'Course Enrollment Confirmation',
        'greeting' => 'Hello :name,',
        'body' => 'Your administrator has enrolled you in the following course:',
        'info' => '<strong>Status:</strong> You have been enrolled and can now access the course materials immediately.',
        'button' => 'Access Course',
        'footer' => 'If you have any questions, please contact your course administrator.',
    ],

    'enrollment_manual_pending' => [
        'subject' => 'Enrollment Notification',
        'title' => 'Enrollment Submitted for Review',
        'greeting' => 'Hello :name,',
        'body' => 'Your administrator has submitted your enrollment for the following course:',
        'info' => '<strong>Status:</strong> Your enrollment is pending review and approval from the course administrator or instructor. You will receive a notification once your enrollment is approved.',
        'confirmation' => 'You will be able to access the course materials once your enrollment is approved.',
        'footer' => 'Thank you for your patience.',
    ],

    'enrollment_approved' => [
        'subject' => 'Enrollment Request Approved',
        'title' => 'Enrollment Request Approved',
        'greeting' => 'Hello :name,',
        'body' => 'Good news! Your enrollment request for the following course has been approved:',
        'info' => '<strong>Status:</strong> You are now actively enrolled and can start accessing the course materials.',
        'button' => 'Access Course',
        'footer' => 'Happy learning!',
    ],

    'enrollment_declined' => [
        'subject' => 'Enrollment Request Declined',
        'title' => 'Enrollment Request Declined',
        'greeting' => 'Hello :name,',
        'body' => 'We regret to inform you that your enrollment request for the following course has been declined:',
        'info' => '<strong>Status:</strong> Your enrollment request has been declined by the course admin or instructor.',
        'contact' => 'If you have any questions regarding this decision, please contact the relevant course admin or instructor.',
        'footer' => 'Thank you for your understanding.',
    ],

    'admin_enrollment_notification' => [
        'subject' => 'New Enrollment Notification',
        'title' => 'New Enrollment Notification',
        'greeting' => 'Hello :name,',
        'body' => 'There is a new enrollment in a course you manage:',
        'course_label' => 'Course',
        'student_label' => 'Student',
        'status_label' => 'Status',
        'status_pending' => 'Pending Approval',
        'status_active' => 'Actively Enrolled',
        'body_pending' => 'Please review and approve or decline this enrollment request via the admin dashboard.',
        'body_active' => 'The student has been actively enrolled in this course.',
        'button_manage' => 'Manage Enrollments',
        'button_view' => 'View Enrollments',
        'footer' => 'This email was sent automatically from the :app_name system.',
    ],

    // Learning Emails
    'assignment_published' => [
        'subject' => 'New Assignment Available',
        'subject_with_details' => 'New assignment: :assignment — :course',
        'title' => 'New Assignment Available',
        'greeting' => 'Hello, <strong>:name</strong>!',
        'body' => 'A new assignment has been published for a course you are enrolled in:',
        'course_label' => 'Course',
        'available_from_label' => 'Available from',
        'deadline_label' => 'Deadline',
        'max_score_label' => 'Maximum Score',
        'submit_text' => 'Please access this assignment and submit your work before the deadline.',
        'button_assignment' => 'View Assignment',
        'button_course' => 'View Course',
        'footer' => 'This email was sent automatically. Please do not reply to this email.',
    ],

    // Schemes Emails
    'course_completed' => [
        'subject' => 'Congratulations! Course Completed',
        'title' => 'Congratulations!',
        'subtitle' => 'You have completed the course!',
        'greeting' => 'Hello, <strong>:name</strong>!',
        'body' => 'We would like to congratulate you for successfully completing the following course:',
        'course_code_label' => 'Course Code',
        'completed_date_label' => 'Completion Date',
        'progress_label' => 'Progress',
        'stat_progress' => 'Progress',
        'stat_completed' => 'Completed',
        'thanks' => 'Thank you for completing this course. We hope you have gained valuable knowledge and skills.',
        'button' => 'View Course',
        'footer' => 'This email was sent automatically. Please do not reply to this email.',
    ],

    // Notifications Emails
    'post_published' => [
        'subject' => 'New Announcement Published',
        'title' => 'New Announcement Published',
        'greeting' => 'Hello :name,',
        'body' => 'A new announcement has been published that may be of interest to you:',
        'post_title_label' => 'Title',
        'category_label' => 'Category',
        'excerpt_label' => 'Preview',
        'button' => 'Read Full Announcement',
        'info' => 'This announcement was published specifically for your role. Click the button above to read the full content.',
        'footer' => 'This email was sent automatically. Please do not reply to this email.',
    ],

    // Common
    'common' => [
        'fallback_url_text' => 'If the button above does not work, copy and paste the following URL into your browser:',
        'thanks' => 'Thank you',
        'regards' => 'Regards',
        'team' => 'The :app_name Team',
    ],
];
