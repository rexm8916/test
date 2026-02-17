@extends('layouts.velzon')

@section('title', 'Edit Payment')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Edit Payment</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('debts.index') }}">Debts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('debts.show', $payment->debt_id) }}">Details</a></li>
                    <li class="breadcrumb-item active">Edit Payment</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
             <div class="card-header">
                <h5 class="card-title mb-0">Update Payment Details</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Customer:</strong> {{ $payment->debt->transaction->customer->name ?? 'N/A' }} <br>
                    <strong>Total Debt:</strong> Rp {{ number_format($payment->debt->amount_total, 0, ',', '.') }}
                </div>

                <form action="{{ route('payments.update', $payment->id) }}" method="POST" id="edit_payment_form">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Payment Amount</label>
                        @php
                            $maxAmount = ($payment->debt->amount_total - $payment->debt->amount_paid) + $payment->amount;
                        @endphp
                        <input type="text" id="amount_display" class="form-control" value="{{ number_format($payment->amount, 0, ',', '.') }}" data-max="{{ $maxAmount }}" oninput="formatNumber(this)" required>
                        <input type="hidden" name="amount" id="amount" value="{{ $payment->amount }}">
                        <div class="form-text">Max allowed: Rp {{ number_format($maxAmount, 0, ',', '.') }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="datetime-local" name="payment_date" id="payment_date" class="form-control" value="{{ $payment->payment_date->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3">{{ $payment->notes }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('debts.show', $payment->debt_id) }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function formatNumber(input) {
        // Remove non-numeric characters
        let value = input.value.replace(/[^0-9]/g, '');
        
        // Convert to integer for formatting (if not empty)
        if (value) {
            value = parseInt(value, 10);
            // Format back to 1.000 structure
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    // On form submit, clean the value and validate
    document.getElementById('edit_payment_form').addEventListener('submit', function(e) {
        let amountInput = document.getElementById('amount_display');
        if (amountInput) {
            let cleanValue = parseInt(amountInput.value.replace(/\./g, '')) || 0;
            let maxAmount = parseInt(amountInput.getAttribute('data-max')) || 0;

            if (cleanValue > maxAmount) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Amount cannot be greater than Rp ' + new Intl.NumberFormat('id-ID').format(maxAmount)
                });
                return;
            }

            document.getElementById('amount').value = cleanValue;
        }
    });

    // Initialize format on load
    document.addEventListener('DOMContentLoaded', function() {
        let amountInput = document.getElementById('amount_display');
        if (amountInput) {
            formatNumber(amountInput);
        }
    });
</script>
@endsection
