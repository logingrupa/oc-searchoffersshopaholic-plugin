# Search Offers Shopaholic Plugin

## Overview

The **Search Offers Shopaholic** plugin is an extension for the [Lovata Shopaholic Plugin](https://octobercms.com/plugin/lovata-shopaholic) in OctoberCMS. 
This plugin provides enhanced search functionality for offers and adds extendable features to manage and display offers efficiently.

Developed by **Logingrupa**.

---

## Features

- Extends the Lovata Shopaholic Plugin to enhance offer search and management.
- Provides custom handlers and components to add flexibility in offer operations.
- Handles additional business logic for offers such as filtering, sorting, and customizing fields.

---

## File Structure

### Main Files

- **`composer.json`**: 
  - Manages dependencies for the plugin.
  - Ensures that necessary packages are available for the plugin to function.
- **`Plugin.php`**: 
  - Registers the plugin within OctoberCMS.
  - Initializes event bindings and component registrations.
- **`plugin.yaml`**: 
  - Metadata file that provides plugin name, description, and dependencies.

---

### Classes

#### `classes/event/ExtendFieldHandler.php`
- **Purpose**:
  - Extends the fields available in the offer model.
  - Adds custom fields dynamically using OctoberCMS's `extend` capabilities.

- **Key Methods**:
  - `extendFields()`: Adds custom fields to the Offer backend form.
    - **Example**: Adding a "Special Promotion" checkbox or "Discount Percentage" field to an offer.

#### `classes/event/OfferModelHandler.php`
- **Purpose**:
  - Hooks into offer-related events in the Shopaholic plugin.
  - Adds custom logic during offer creation, updates, or deletion.

- **Key Methods**:
  - `onOfferCreate()`: Executes specific logic when a new offer is created.
    - **Example**: Automatically associating the offer with a specific category or tag.
  - `onOfferUpdate()`: Validates or updates custom fields when an offer is modified.
  - `onOfferDelete()`: Ensures related data (e.g., logs, metrics) are cleared when an offer is deleted.

---

### Components

#### `components/OfferList.php`
- **Purpose**:
  - Renders a list of offers on the front-end with search and filtering capabilities.

- **Key Properties**:
  - `offerFilter`: Allows the user to specify conditions for filtering offers (e.g., by category, tag, or price).
  - `offerSort`: Determines how offers are sorted (e.g., by price ascending/descending, newest first).

- **Key Methods**:
  - `onRun()`: Initializes the component and fetches filtered offers to display.
  - `getFilteredOffers()`: Fetches offers based on the filter and sorting options.
    - **Example**: Return only offers with a discount of 20% or more within a specific price range.
  - `onSearch()`: Handles AJAX search requests from the front-end.

---

### Localization

#### `lang/en/lang.php`
- **Purpose**:
  - Contains English translations for labels, messages, and other text used in the plugin.

- **Key Content**:
  - Strings for offer-related fields, messages, and errors.
  - Example:
    ```php
    'offer_list_title' => 'List of Offers',
    'no_offers_found' => 'No offers found matching your criteria.',
    ```

---

### Updates

#### `updates/version.yaml`
- **Purpose**:
  - Tracks plugin versions and their associated database changes or migrations.

- **Key Content**:
  - Records schema changes for offers, such as adding or modifying fields.

  **Example**:
  ```yaml
  1.0.0:
    - Initial release.
    - Added custom fields for offers.
---

### Need Help?

If you encounter any issues, please feel free to open an issue on this repository.