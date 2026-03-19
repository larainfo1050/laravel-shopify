Laravel Technical Assessment
CSV to Shopify Product Import System
Project Requirements
Create a Laravel 12 application that allows users to upload CSV files containing
product data, process them asynchronously, import the products to Shopify, and
track the import status.
Task Breakdown
1. Project Setup
● Initialize a new Laravel 12 project
● Configure database connections
● Set up Shopify API credentials in the environment file
● Install necessary packages
2. File Upload Interface
● Create a form that allows users to upload CSV files
● Implement client-side validation for file type and size
● Design a clean, responsive interface
● Add feedback mechanisms for successful uploads
3. CSV Processing and Shopify Integration
● Create a database migration to save product records
● Implement a Laravel job for processing CSV files asynchronously
● Parse CSV data and validate the format
● Map CSV columns to Shopify product fields
● Create API integration with Shopify to add products
4. Dashboard
● Build a dashboard to display all imports
● Show status for each product (pending, processing, successful, failed)
● Display error messages for failed upload
5. Bonus Features
● Logging: Implement comprehensive logging across the application
○ Log all import events
○ Create a log viewer in the dashboard
○ Implement error notification system
● GraphQL API:
○ Use GraphQL for the Shopify integration instead of REST
○ Create GraphQL queries and mutations for product import
○ Implement error handling for GraphQL responses
● Update Product
○ If product already exists update records
Technical Requirements
Database
● Create appropriate migrations for:
○ Upload records
○ Product import status
○ Error logs
Models
● Design models with appropriate relationships for:
○ Uploads
○ Products
○ Import Records
Jobs and Queues
● Implement Laravel queue system for background processing
● Create jobs for handling CSV imports
Frontend
● Use Blade templates or a modern JavaScript framework (ReactJS, Vue)
Submission Guidelines
1. Push your code to a GitHub repository
2. Include a README with:
○ Setup instructions
○ Overview of your implementation
○ Any assumptions or design decisions
○ Instructions for testing the application
3. Include video of the working application