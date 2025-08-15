# Application Features

This document outlines the key features of the Laravel application, categorized by functionality.

## I. Core User Account & Authentication

### A. User Registration
*   New users can create an account.
*   Required information: Name, Email, Password, Preferred Locale.
*   Automatic login after successful registration.
*   **Group Invitation Integration**:
    *   If registering via an invitation link, the email may be pre-filled.
    *   Users are automatically added to the respective group upon registration through an invite.
*   Accounts are activated by default upon creation.

### B. User Login & Logout
*   Standard authentication using email and password.
*   "Remember Me" option for persistent sessions.
*   Secure logout functionality.

### C. Password Management
*   **Forgot Password**: Users can request a password reset link to be sent to their email.
*   **Reset Password**: Users can set a new password using a secure tokenized link from the reset email.
*   **Update Password (Logged-in User)**: Authenticated users can change their password from their profile settings (requires current password verification).
*   **Confirm Password**: Certain sensitive actions within the application may require users to re-enter their password for security.

### D. Email Verification
*   Users receive a verification email upon registration.
*   In-app prompts to verify email if not yet completed.
*   Option to resend the verification link.
*   Access to key features (e.g., Dashboard) is contingent upon email verification.

### E. User Profile Management (Self-Service)
*   Users can view and update their profile information, including name and email address.
*   Users can set their preferred locale, which dictates the application's display language.
*   Changing the registered email address triggers a re-verification process for the new email.
*   Users can delete their own account (requires password confirmation for security).

## II. Wishlist Management

### A. Wishlist Creation & Configuration
*   Users can create an unlimited number of personal wishlists.
*   Each wishlist requires a **Name**.
*   Optional fields include a **Description** and an **Expiration Date** (useful for events or time-sensitive wishlists).
*   Wishlists are created as private by default (`is_shared = false`).
*   Wishlists can be marked with `can_be_duplicated` (default: false) to allow or disallow other users from creating a copy.

### B. Wishlist Viewing & Listing
*   Users can view all wishlists they own.
*   Users can view wishlists that have been shared with them (typically via groups).
*   Wishlist views are paginated for better performance and include sorting options.
*   Filtering options:
    *   "My Wishlists" vs. "All" (includes shared/public wishlists).
    *   Filter by expiration date.

### C. Wishlist Editing & Deletion
*   Users have full control to edit the details of wishlists they own.
*   Users can delete their own wishlists.

### D. Wishlist Duplication
*   Users can duplicate an existing wishlist if:
    *   The wishlist is marked as `can_be_duplicated`.
    *   They are the owner of the wishlist.
*   Duplicated wishlists are appended with "(Copy)" to their name.
*   The new copy receives a default expiration date and is set to private and non-duplicable by default.
*   All items from the source wishlist are copied to the new wishlist, with their "claimed" (`in_shopping_list`) and "bought" (`is_bought`) statuses reset.

### E. Wishlist Sharing
*   Primary sharing mechanism is through **Groups** (see Group Management).
*   Wishlists have an `is_shared` flag, potentially for broader sharing if not tied to a private group.
*   *(Future consideration noted in code: notify group members when a wishlist is shared).*

### F. Wishlist Item Management
*   **Adding Items**: Users can add multiple items to their wishlists.
    *   Item details: Name, Description (optional), URL Link (optional, for online stores), Price (optional), Priority level.
*   **Viewing Items**: Items are displayed as part of their parent wishlist.
    *   Detailed view for individual items (potentially a modal or separate page).
*   **Editing & Deleting Items**: Users can edit or remove items from wishlists they own.
*   **Item Priority**: Assign priority to items to indicate their importance.
*   **Marking Item as Bought**: Items can be marked as `is_bought`. This status can be toggled.
*   **Assigning/Claiming Items (Shopping List Feature)**:
    *   For shared wishlists (e.g., within groups), users can "claim" an item they intend to purchase for the wishlist owner.
    *   Claiming an item links it to the user's ID and marks it as `in_shopping_list = true`.
    *   Users can "unclaim" an item they previously linked.
    *   System events are triggered when an item's claim status changes.
*   **Pagination**: Items within a wishlist view are paginated.

## III. Group Management & Sharing

### A. Group Creation & Configuration
*   Users can create groups.
*   Required field: **Name**. Optional: **Description**.
*   Groups are **private** by default (`is_private = true`), meaning they are invite-only.
*   The user who creates the group is automatically assigned the 'owner' role.

### B. Group Viewing & Listing
*   Users can view all groups they are a member of.
*   Public groups (`is_private = false`) can be listed and potentially joined (current implementation focuses on private groups).
*   Group lists are paginated and offer sorting options.

### C. Group Editing & Deletion
*   Group owners (and potentially other authorized roles) can edit group details.
*   Group owners can delete groups they manage.

### D. Group Membership Management
*   **Adding Members**: Group owners/admins can add existing application users to their groups.
*   **Removing Members**: Group owners/admins can remove users from their groups.
*   **Roles**: Users have defined roles within a group (e.g., 'owner', 'member').
*   **Permissions**: Specific members may have permission to invite other users to the group (`can_invite_users` flag).
*   **Member List**: View a paginated list of group members, including their roles and pending invitations.

### E. Group Invitation System
*   **Email Invitations**:
    *   Authorized group members can send invitations to join the group via email.
    *   Email invitations are tokenized and have an expiration time (e.g., 24 hours).
*   **Link Invitations**:
    *   Authorized group members can generate a shareable invitation link.
    *   Link-based invitations are also tokenized and have a shorter expiration time (e.g., 10 minutes).
    *   If a valid, non-expired link invite already exists for the group, it's returned instead of generating a new one.
*   **Accepting Invitations**:
    *   **Existing Users**: Authenticated users who click an invitation link (or the link in an invitation email) and accept are added to the group.
    *   **New Users**: Unauthenticated users accessing an invitation link are redirected to the registration page. Upon successful registration, they are automatically added to the group. The email field may be pre-filled if the invitation was email-based.
*   **Managing Invitations**:
    *   Group owners can view and manage pending invitations for their groups.
    *   Pending invitations can be cancelled/deleted by group owners. A notification is sent to the invitee if their invitation is revoked.
    *   A scheduled background task (`app:clean-up-old-unaccepted-group-invitations`) automatically removes expired and unaccepted invitations from the system.
    *   Users can view a list of invitations they have personally received.

### F. Group Gifts
*   **Member contribution**:
    *   Authorized group members can contribute to the gift amount once they added the gift to their "Shopping List".
    *   If they remove the item from their "Shopping List", they are removed from the group.
    *   Last member to remove the item from their list will also delete the group.
    *   First user to add the item will create the group automatically.

## IV. Dashboard

*   A personalized dashboard is available for all logged-in and email-verified users.
*   The dashboard primarily displays a paginated list of wishlist items that the user has "claimed" (i.e., items they intend to purchase from various wishlists, marked as `in_shopping_list` and linked to their `user_id`), and it also works for group gifts.
*   Sorting options are available for the dashboard items (e.g., sort by wishlist owner's name, wishlist name, or other item properties).

## V. User-Specific Features & Notifications

### A. Preferred Locale
*   Users can select their preferred language for the application interface from their profile settings.

### B. Birthday Notifications
*   Users can optionally store their birthday in their profile.
*   Users can opt-in or opt-out of receiving birthday notifications.
*   A scheduled background task (`app:notify-users-of-upcoming-birthdays`) automatically sends a notification 14 days in advance to users who have opted-in and have an upcoming birthday.

## VI. Administrative Features

These features are typically accessible only to users with administrative privileges.

### A. User Management (Admin Panel)
*   **List Users**: View a comprehensive, paginated, and sortable list of all users in the system.
*   **View User Details**: Access detailed information for any specific user.
*   **Create New Users**:
    *   Manually create new user accounts.
    *   Set user details: name, email, password (can be auto-generated if not provided), preferred locale, active status, and admin status.
    *   Notifications are sent to newly created users.
*   **Edit Existing Users**:
    *   Modify details for any user: name, email, preferred locale, active status, admin status.
    *   Changing a user's email address will mark it as unverified, requiring the user to re-verify.
    *   Notifications can be triggered for changes in account state (e.g., activated/deactivated) or if a user is granted admin privileges.
*   **Delete Users**: Remove user accounts from the system.
*   **Update User Passwords**: Admins can change the password for any user account.

### B. Admin Role
*   A boolean `is_admin` flag on the user model distinguishes administrators.
*   Admin-specific routes and functionalities are protected by `OnlyForAdminRequests` middleware.

## VII. System & Background Features

### A. Caching
*   The application utilizes Laravel's caching system (configured with the database driver) to improve performance.

### B. Queued Jobs
*   Leverages Laravel's queue system (with the database driver) for handling background tasks asynchronously, such as sending emails.
*   Supports job batching for managing groups of jobs.
*   Failed jobs are logged for monitoring and potential retries.

### C. Artisan Commands (Scheduled Tasks)
*   `app:clean-up-old-unaccepted-group-invitations`: Periodically runs to maintain data hygiene by removing expired and unaccepted group invitations.
*   `app:notify-users-of-upcoming-birthdays`: Periodically runs to send out birthday reminder notifications.

### D. Localization (i18n)
*   The application is built to support multiple languages, with English as the default. User language preference is stored and respected.

### E. Frontend Technology
*   The user interface is built using Vue.js components, rendered server-side via Inertia.js, providing a responsive and modern single-page application experience.

## License
The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
