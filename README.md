Mofab Metric bundle
=========

A Symfony bundle for doing metrics within an application.

Installation
------------

Add the bundle to your `composer.json` file:

    php composer.phar require "Mofab/Metric-Bundle" "dev-master"

Register the bundle in `app/AppKernel.php`:

``` php
public function registerBundles()
{
    $bundles = [
        // ...
        new \Mofab\MetricBundle\MofabMetricBundle(),
    ];
}
```

Configuration
-------------

Register the MetricHelper as a service in services.yml:

```
metric_helper:
  class: Mofab\MetricBundle\Helpers\MetricHelper
  arguments:
    - "@doctrine"
    - "@logger"
  calls:
    - method: "setApplication"
      arguments: [ 'myApplication' ]
```

Create the necessary database table:

    php bin/console doctrine:schema:update --force

Usage
-------------
Use the Metric helper in any function at any time. For example in a controller:

    $this->get('metric_helper')->start(__METHOD__, 'myUnit');

Update the quantity while you have work in progress:

    $this->get('metric_helper')->updateQuantity($count, __METHOD__);

Complete the metric when you're done (optional):

    $this->get('metric_helper')->complete(__METHOD__, $count);

You can start multiple metrics at the same time:

    $this->get('metric_helper')->start('metric1', 'myUnit');
    $this->get('metric_helper')->start('metric2', 'myUnit');
    $this->get('metric_helper')->updateQuantity(12, 'metric2');
    
But beware for using the correct script names:

    $this->get('metric_helper')->start('metric1', 'myUnit');
    $this->get('metric_helper')->updateQuantity(12, 'metric2');

    Error in the log: Script "metric2" is trying to update quantity to 12 on non existing metric

