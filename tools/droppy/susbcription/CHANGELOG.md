V2.1.8
- Added option to set plan inactive
- Fixed issue with Stripe not updating the plan details properly
- Fixed issue with Stripe not always setting the start/end date properly
- Fixed rounding issue when voucher was applied.
- Fixed issue that caused subscription not to be loaded when active
- Updated Italian translation.

V2.1.7
- Fixed users page crash on some MySQL servers
- Fixed price per period showing incorrect when more than 1 month and less than 1 year
- Fixed decimal price issue
- Improved security in authentication
- Updated Stripe SDK to latest version

V2.1.6
- Added support for custom backgrounds
- Fixed limit issue on the amount of upload expire time options
- Fixed incorrect display of expire time when set to unlimited
- Fixed display issue for uploads that never expired
- Fixed issue that caused deleted uploads to show in sub-user uploads list
- Fixed dubble confirm delete popup

V2.1.5
- Fixed issue where 12 month plan wasn't shown as Yearly plan
- Fixed display issue when more than 3 plans are available
- Made subscription status value translatable

V2.1.4
- Added new option maximum storage option to plans (You can now set a maximum amount of active upload storage that is allowed per account)

V2.1.3
- Fixed issue with subscription creation form in the admin panel
- Fixed issue that caused free plans to not work properly
- Fixed issue where paypal logo was shown when Stripe was selected as payment method
- Added extra notice to sub-user creation form
- Added plan ID to subscription list
- Added some extra checks to the admin panel when setting up plans and the payment gateway

V2.1.2
- Added option for multiple premium packages
- Redesigned registration page and made it available on the home page
- Fixed issue that caused a subscription to be cancelled too soon
- Fixed issue that caused all sub-users their emails to change when the parent account changed their email
- Fixed issue that caused total download of user not to show properly in the admin panel
- Fixed issue that made it impossible to cancel a stripe subscription using the admin panel
- Fixed incorrect currency shown on voucher page in the admin panel
- Fixed some database installation issues
- Dropped support for old Droppy themes (default, grey and oldtimer)

V2.1.1
- Added option for multiple expiration times
- Fixed issue where reset password form wouldn't work
- Fixed issue where subscriptions were not cancelled after their expiration time

V2.1.0
- Changes for new admin panel

V2.0.10
- Sub users now share uploads with each other
- New uploads will automatically be added to uploads list without refresh
- Added extra info text to sub-users tab