# Ves_Trackorder Pro version

With our Magento 2 Order Status Tracking, you can let your customers track their order status right after they complete their payment for their purchase. Customers can do the tracking with just the order ID and their email address without any need to log in.

## Features

- Tracking order information Without Login FEATURED
- Send order information to email
- Customer can send order info to any other email address
- Generate order QR codes to check order status FEATURED
- Add link on main-menu & top link
- Show shipment tracking link and tracking code
- Display custom messages if order is not found
- Reorder without login
- Easily tracking order summary in the front end
- Support for all the Magento Product Types
- Your customers can easily track
- Track order status, print order directly
- Attach Invoice PDF File To Email
- Mobile & tablet Optimized
- Place anywhere with widget support
- Get Track Code 

## How to installation

1. Setup module via FTP and run magento 2 commands:

The extension include 2 module: Ves_All and Ves_Trackorder

- Connect your server with FTP client (example: FileZilla).
- Upload module files in the module packages in to folder: app/code/Ves/Trackorder , app/code/Ves/All
- Access SSH terminal, then run commands:

```
php bin/magento setup:upgrade --keep-generated
php bin/magento setup:static-content:deploy -f
php bin/magento cache:clean
```
- To config the module please. Go to admin > Store > Configuration > Venustheme - Extensions > Track Order

Default module settings as this:
![Order Tracking Settings](./assets/order_tracking_settings.png)

## Support Console Commands
1. Generate tracking code for old orders:
```
php bin/magento vestrackorder:generate order_status
```
order_status (optional): allow filter orders matched with the status. Empty value or set "all" to generate for all orders.

example:

```
php bin/magento vestrackorder:generate pending
```

## Require Extensions
- venustheme/module-all
