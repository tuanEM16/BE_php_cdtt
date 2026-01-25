<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class OrderSuccessEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $order;
    public $total;
    public function __construct($order)
    {
        $this->order = $order;
        $this->total = $order->orderdetails->sum(function ($d) {
            if (!is_null($d->amount)) return (float)$d->amount;
            $price = (float)($d->price ?? 0);
            $qty = (int)($d->qty ?? 0);
            $discount = (float)($d->discount ?? 0);
            return max($price - $discount, 0) * $qty;
        });
    }
    public function build()
    {
        return $this->subject('Thanh toán thành công - Đơn hàng #' . $this->order->id)
            ->markdown('emails.orders.success');
    }
}
