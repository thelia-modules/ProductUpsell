# Product Upsell

Sale more by offering upsell products to your customers if the cart total is greater than a defined amount.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ProductUpsell.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require thelia/product-upsell-module:~0.9
```

## Usage

Go to module configuration and select the category in which you'll store your upsell products. It's better to put this category offline
to hide it from your customers.

Create one or more products in this category and in the product edition page, enter the cart amount which allows to offer this product


## Loop

### productupsell Loop

The loop return upsell products.

### Input arguments

|Argument |Description |
|---      |--- |
|**id** | filter by upsell product id |
|**product_id** | filter by product id |
|**cart_amount** | filter by upsell product with minimum cart amount less or equal to this value |
|**limit** | limit the number of results |

### Output arguments

|Variable   |Description |
|---        |--- |
|PRODUCT_UPSELL_ID    | upsell product id |
|PRODUCT_ID    | product id |
|MINIMUM_CART_AMOUNT    |  upsell product minimum cart amount |

### Exemple

```
{loop type="productupsell" name="productupsell_loop" cart_amount="30" limit=4}
    {loop type="product" name="product_loop"  limit=1 id=$product_page order=$product_order}
        {include file="includes/single-product.html" hovered="true"}
    {/loop}
{/loop}
```

### productupsell.category Loop

The loop return the upsell category ID if the given category ID is equal to the upsell category ID, or nothing.

### Input arguments

|Argument |Description |
|---      |--- |
|**category_id** | a category id |

### Output arguments

|Variable   |Description |
|---        |--- |
|UPSELL_CATEGORY_ID    | upsell category id |
