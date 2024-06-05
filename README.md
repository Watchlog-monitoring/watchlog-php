
# Watchlog Usage Documentation

## Introduction
The `Watchlog` class is a simple PHP class for sending various types of metrics to a server. It uses the Guzzle HTTP client for making asynchronous HTTP requests.

## Setup
1. **Install Watchlog:** Ensure you have the Watchlog package installed via Composer. Run the following command in your project directory:
   ```bash
   composer require metrics-tracker/watchlog
   ```

2. **Include the `Watchlog` Class:**
   Ensure your PHP script includes the `Watchlog` class. Use an autoloader or include the class file directly.

## Usage
Here are the methods available in the `Watchlog` class and how to use them:

1. **Increment a Metric:**
   ```php
   use MetricsTracker\Watchlog;

   $watchlog = new Watchlog();
   $watchlog->increment('page_views');
   // Optionally increment by a different value
   $watchlog->increment('page_views', 5);
   ```

2. **Decrement a Metric:**
   ```php
   $watchlog->decrement('active_users');
   // Optionally decrement by a different value
   $watchlog->decrement('active_users', 2);
   ```

3. **Set a Gauge Value:**
   ```php
   $watchlog->gauge('memory_usage', 512);
   ```

4. **Set a Percentage Value:**
   ```php
   $watchlog->percentage('cpu_usage', 75);
   ```

5. **Set a System Byte Value:**
   ```php
   $watchlog->systembyte('disk_space', 1024000);
   ```

## Notes
- Ensure the server agent is set up to handle the incoming metric requests.
- Each method (`increment`, `decrement`, `gauge`, `percentage`, `systembyte`) sends a metric to the server with the specified method, metric name, and value.
- The `sendMetric` method is a private method used internally to handle the HTTP request logic.

This documentation should help you understand how to set up and use the `Watchlog` class for tracking metrics in your PHP application.
