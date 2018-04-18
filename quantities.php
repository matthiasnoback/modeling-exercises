<?php

require __DIR__ . '/test.php';

class SalesOrderLine
{
    /**
     * @var float
     */
    private $quantityInitiallyOrdered;

    /**
     * @var float
     */
    private $quantitySoFarDelivered;

    /**
     * @var float
     */
    private $quantityOpen;

    /**
     * @var int
     */
    private $quantityPrecision = 2;

    /**
     * @param float $quantityInitiallyOrdered
     */
    public function __construct($quantityInitiallyOrdered)
    {
        $this->quantityInitiallyOrdered = $quantityInitiallyOrdered;
        $this->quantitySoFarDelivered = 0.0;
        $this->quantityOpen = $quantityInitiallyOrdered;
    }

    /**
     * @param float $quantityInCurrentDelivery
     * @return SalesOrderLine
     */
    public function processDelivery($quantityInCurrentDelivery)
    {
        $this->quantitySoFarDelivered = round($this->quantitySoFarDelivered + $quantityInCurrentDelivery, $this->quantityPrecision);

        $this->quantityOpen = $this->quantityInitiallyOrdered - $quantityInCurrentDelivery;
        if ($this->quantityOpen < 0) {
            // over-delivering is fine, but we'll set quantity open to 0
            $this->quantityOpen = 0.0;
        }

        return $this;
    }

    /**
     * @param $quantityInCurrentDelivery
     * @return $this
     */
    public function undoDelivery($quantityInCurrentDelivery)
    {
        $this->quantitySoFarDelivered = round($this->quantitySoFarDelivered - $quantityInCurrentDelivery, $this->quantityPrecision);
        if ($this->quantitySoFarDelivered < 0) {
            $this->quantitySoFarDelivered = 0.0;
        }

        $this->quantityOpen = round($this->quantityOpen + $quantityInCurrentDelivery, $this->quantityPrecision);
        if ($this->quantityOpen > $this->quantityInitiallyOrdered) {
            // maybe we have been over-delivered, but we shouldn't expect more than we ordered
            $this->quantityOpen = $this->quantityInitiallyOrdered;
        }

        return $this;
    }

    public function getQuantityInitiallyOrdered()
    {
        return $this->quantityInitiallyOrdered;
    }

    public function getQuantitySoFarDelivered()
    {
        return $this->quantitySoFarDelivered;
    }

    public function getQuantityOpen()
    {
        return $this->quantityOpen;
    }

    public function getQuantityPrecision()
    {
        return $this->quantityPrecision;
    }
}

$line = new SalesOrderLine(12.5);

it('increases the quantity so far delivered upon processing a delivery',
    (new SalesOrderLine(12.5))->processDelivery(10.0)->getQuantitySoFarDelivered() === 10.0
);
it('decreases the quantity still open upon processing a delivery',
    (new SalesOrderLine(12.5))->processDelivery(10.0)->getQuantityOpen() === 2.5
);
it('decreases the quantity so far delivered upon undoing a delivery',
    (new SalesOrderLine(12.5))->processDelivery(12.5)->undoDelivery(5.0)->getQuantitySoFarDelivered() === 7.5
);
it('increases the quantity open upon undoing a delivery',
    (new SalesOrderLine(12.5))->processDelivery(12.5)->undoDelivery(5.0)->getQuantityOpen() === 5.0
);
it('does not let quantity open go below 0',
    (new SalesOrderLine(12.5))->processDelivery(15.0)->getQuantityOpen() === 0.0
);
it('does not let quantity remaining to be delivered go over the initially ordered quantity',
    (new SalesOrderLine(12.5))->undoDelivery(15.0)->getQuantityOpen() === 12.5
);
done();

/*
 * Design issues that should be fixed by introducing value objects:
 *
 * - Dealing with "quantity precision" (i.e. number of decimals taken into account).
 * - Preventing "quantity so far delivered" to end up being less than 0.
 * - Preventing "quantity open" to end up being more than "quantity initially ordered".
 *
 * Question: what is the relation between these different quantities? Which ones do we need to "remember" (i.e. store in a database, etc.), which ones can we derive/compute?
 */
