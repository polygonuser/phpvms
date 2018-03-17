<?php
/**
 * Based on https://github.com/scottlaurent/accounting
 * With modifications for phpVMS
 */

namespace App\Models;

use App\Support\Money;
use Carbon\Carbon;

/**
 * Class Journal
 * @property mixed id
 * @property Money $balance
 * @property string $currency
 * @property Carbon $updated_at
 * @property Carbon $post_date
 * @property Carbon $created_at
 * @property \App\Models\Enums\JournalType type
 */
class Journal extends BaseModel
{
    protected $table = 'journals';

    public $fillable = [
        'ledger_id',
        'journal_type',
        'balance',
        'currency',
        'morphed_type',
        'morphed_id',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'updated_at'
    ];

    /**
     * Get all of the morphed models.
     */
    public function morphed()
    {
        return $this->morphTo();
    }

    /**
     * @internal Journal $journal
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    protected static function boot()
    {
        static::created(function (Journal $journal) {
            $journal->resetCurrentBalances();
        });

        parent::boot();
    }

    /**
     * Relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(JournalTransaction::class);
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param Ledger $ledger
     * @return Journal
     */
    public function assignToLedger(Ledger $ledger)
    {
        $ledger->journals()->save($this);
        return $this;
    }

    /**
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function resetCurrentBalances()
    {
        $this->balance = $this->getBalance();
        $this->save();
    }

    /**
     * @param $value
     * @return Money
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function getBalanceAttribute($value): Money
    {
        return new Money($value);
    }

    /**
     * @param $value
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function setBalanceAttribute($value): void
    {
        $value = ($value instanceof Money)
            ? $value
            : new Money($value);

        $this->attributes['balance'] = $value ? (int)$value->getAmount() : null;
    }

    /**
     * @param Journal $object
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactionsReferencingObjectQuery($object)
    {
        return $this
            ->transactions()
            ->where('ref_class', \get_class($object))
            ->where('ref_class_id', $object->id);
    }

    /**
     * Get the credit only balance of the journal based on a given date.
     * @param Carbon $date
     * @return Money
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function getCreditBalanceOn(Carbon $date)
    {
        $balance = $this->transactions()
            ->where('post_date', '<=', $date)
            ->sum('credit') ?: 0;

        return new Money($balance);
    }

    /**
     * Get the balance of the journal based on a given date.
     * @param Carbon $date
     * @return Money
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function getBalanceOn(Carbon $date)
    {
        return $this->getCreditBalanceOn($date)
                    ->subtract($this->getDebitBalanceOn($date));
    }

    /**
     * Get the balance of the journal as of right now, excluding future transactions.
     * @return Money
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function getCurrentBalance()
    {
        return $this->getBalanceOn(Carbon::now());
    }

    /**
     * Get the balance of the journal.  This "could" include future dates.
     * @return Money
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function getBalance()
    {
        $balance = $this
                ->transactions()
                ->sum('credit') - $this->transactions()->sum('debit');

        return new Money($balance);
    }
}
