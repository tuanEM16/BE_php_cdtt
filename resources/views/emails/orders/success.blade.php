@component('mail::message')
# Thanh toán thành công 🎉
Chào **{{ $order->name ?? 'bạn' }}**,
Shop đã nhận thanh toán cho **đơn hàng #{{ $order->id }}**.
**SĐT:** {{ $order->phone ?? '' }}
**Email:** {{ $order->email ?? '' }}
**Địa chỉ:** {{ $order->address ?? '' }}
---
## Chi tiết đơn hàng
@foreach ($order->orderdetails as $d)
    - **{{ $d->product->name ?? 'Sản phẩm' }}**
    - SL: {{ $d->qty }}
    - Giá: {{ number_format($d->price ?? 0) }} đ
    @if(($d->discount ?? 0) > 0)
        - Giảm: {{ number_format($d->discount) }} đ
    @endif
    - Thành tiền: {{ number_format($d->amount ?? (($d->price - ($d->discount ?? 0)) * $d->qty)) }} đ
@endforeach
---
**Tổng thanh toán:** **{{ number_format($total) }} đ**
Cảm ơn bạn đã mua hàng!
{{ config('app.name') }}
@endcomponent