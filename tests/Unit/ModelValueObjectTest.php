<?php


namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;
use Tests\ValueObjects\Address;
use Tests\ValueObjects\Email;

class ModelValueObjectTest extends TestCase
{
    public function testCreateModel()
    {
        $account = $this->accountModel();

        $name    = 'Example Test';
        $email   = Email::create('test@gmail.com');
        $address = Address::create(['country' => 'Russia', 'city' => 'Moscow', 'street' => 'Red Square', 'number' => '1']);


        $account->query()
            ->create(compact('name', 'email', 'address'));

        $this->assertDatabaseHas('accounts',
            [
                'name'  => 'Example Test',
                'email' => 'test@gmail.com',
            ]);
    }

    public function testEquals()
    {
        $account = $this->accountModel();

        $account->name    = 'Example Test';
        $account->email   = Email::create('test@gmail.com');
        $account->address = $address = Address::create(['country' => 'Russia', 'city' => 'Moscow', 'street' => 'Red Square', 'number' => '1']);
        $account->save();

        $this->assertEquals($account->fresh()->address, $address);
    }

    public function testAttributes()
    {
        $account = $this->accountModel();

        $account->name    = 'Example Test';
        $account->email   = $email = Email::create('test@gmail.com');
        $account->address = Address::create(['country' => 'Russia', 'city' => 'Moscow', 'street' => 'Red Square', 'number' => '1']);
        $account->save();

        $this->assertEquals($email->domain(), $account->fresh()->domain);
        $this->assertEquals(e($email), $account->fresh()->email->value());
    }

    private function accountModel()
    {
        return new class extends Model {
            protected $table = 'accounts';

            protected $fillable = ['name', 'email', 'address'];

            protected $casts = ['email' => Email::class, 'address' => Address::class];

            public function getDomainAttribute()
            {
                return $this->email->domain();
            }
        };
    }
}
