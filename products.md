# GraphQL API - Complete CRUD Documentation

**GraphQL Playground:** http://localhost:8000/graphql-playground

---

## 📚 Quick Reference

| Operation | Mutation/Query | Description |
|-----------|----------------|-------------|
| **CREATE** | `createProduct` | Create new product |
| **READ ALL** | `products` | Get all products |
| **READ ONE** | `product(id)` | Get single product |
| **UPDATE** | `updateProduct(id)` | Update product |
| **DELETE** | `deleteProduct(id)` | Delete product |

---

## 1️⃣ CREATE Product

### Basic Create
```graphql
mutation CreateProduct {
  createProduct(
    upload_id: 1
    handle: "test-product-001"
    title: "Test Product"
    published: true
    variant_price: 19.99
    variant_inventory_qty: 100
  ) {
    id
    handle
    title
    variant_price
    variant_inventory_qty
    created_at
  }
}

mutation CreateFullProduct {
  createProduct(
    upload_id: 1
    handle: "premium-blue-shirt"
    title: "Premium Blue Shirt"
    body_html: "<p>High quality cotton shirt in ocean blue</p>"
    vendor: "Fashion Store"
    product_type: "Apparel"
    tags: "shirt, blue, premium, cotton"
    published: true
    variant_sku: "SHIRT-BLUE-001"
    variant_price: 45.99
    variant_compare_at_price: 59.99
    variant_requires_shipping: true
    variant_taxable: true
    variant_inventory_tracker: "shopify"
    variant_inventory_qty: 75
    variant_inventory_policy: "deny"
    variant_fulfillment_service: "manual"
    variant_weight: 0.5
    variant_weight_unit: "kg"
    image_src: "https://example.com/blue-shirt.jpg"
    image_position: 1
    image_alt_text: "Premium Blue Cotton Shirt"
    import_status: "successful"
  ) {
    id
    handle
    title
    vendor
    variant_price
    variant_inventory_qty
    published
    created_at
  }
}

mutation UpdateFull {
  updateProduct(
    id: 1
    handle: "updated-handle"
    title: "Updated Title"
    body_html: "<p>Updated body</p>"
    vendor: "New Vendor"
    product_type: "New Type"
    tags: "tag1, tag2"
    published: true
    variant_sku: "NEW-SKU-001"
    variant_price: 99.99
    variant_compare_at_price: 129.99
    variant_requires_shipping: true
    variant_taxable: true
    variant_inventory_tracker: "shopify"
    variant_inventory_qty: 200
    variant_inventory_policy: "continue"
    variant_fulfillment_service: "manual"
    variant_weight: 1.5
    variant_weight_unit: "kg"
    image_src: "https://example.com/new-image.jpg"
    image_position: 1
    image_alt_text: "New Image"
    import_status: "successful"
  ) {
    id
    handle
    title
    variant_price
    variant_inventory_qty
    updated_at
  }
}

query GetAllProducts {
  products {
    id
    handle
    title
    vendor
    variant_price
    variant_inventory_qty
    published
    import_status
    created_at
  }
}

query GetProduct {
  product(id: 1) {
    id
    handle
    title
    body_html
    vendor
    product_type
    tags
    published
    variant_sku
    variant_price
    variant_compare_at_price
    variant_requires_shipping
    variant_taxable
    variant_inventory_tracker
    variant_inventory_qty
    variant_inventory_policy
    variant_fulfillment_service
    variant_weight
    variant_weight_unit
    image_src
    image_position
    image_alt_text
    import_status
    error_message
    created_at
    updated_at
    upload {
      id
      original_filename
      status
    }
  }
}
mutation UpdatePrice {
  updateProduct(
    id: 1
    variant_price: 29.99
  ) {
    id
    handle
    title
    variant_price
    updated_at
  }
}
