@extends('layout.app', ['showLayout' => false, 'isPrint' => true, 'title' => 'Business Summary Report'])

@section('content')
    <!-- INTERNAL HEADER -->
    <div class="mb-10">
        <div class="flex justify-between items-start mb-6">
            <div class="space-y-1">
                <h1 class="text-huge">{{ get_setting('APP_NAME', 'Mini Lara') }}</h1>
                <p class="text-[12px] font-bold text-tp uppercase tracking-tighter">Business Performance Summary</p>
            </div>
            <div class="text-right">
                <div class="px-6 py-2 bg-black text-white inline-block font-black text-xs uppercase rounded-lg mb-2">Summary Report</div>
                <div class="text-[10px] font-bold">Generated: {{ date('d M, Y h:i A') }}</div>
            </div>
        </div>
        <div class="border-thick mb-10"></div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-6 mb-10">
        <div class="border border-black p-4 text-center">
            <div class="text-[10px] font-bold uppercase mb-1">Total Gross Revenue</div>
            <div class="text-xl font-bold">Rs. 1,500,000.00</div>
        </div>
        <div class="border border-black p-4 text-center">
            <div class="text-[10px] font-bold uppercase mb-1">Total Expenses</div>
            <div class="text-xl font-bold">Rs. 950,000.00</div>
        </div>
        <div class="border border-black p-4 text-center bg-gray-50">
            <div class="text-[10px] font-bold uppercase mb-1">Net Realized Profit</div>
            <div class="text-xl font-bold">Rs. 550,000.00</div>
        </div>
    </div>

    <table class="w-full border-collapse mb-10">
        <thead>
            <tr class="table-header-gray">
                <th class="py-4 px-4 text-left border-black">Description</th>
                <th class="py-4 px-4 text-center border-black">Quantity</th>
                <th class="py-4 px-4 text-right border-black">Net Profit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <tr>
                <td class="py-4 px-4 text-xs font-bold uppercase">Dummy Report Item A</td>
                <td class="py-4 px-4 text-center font-bold text-xs">150</td>
                <td class="py-4 px-4 text-right font-bold text-xs">Rs. 100,000</td>
            </tr>
        </tbody>
    </table>

    <!-- INTERNAL FOOTER -->
    <div class="mt-auto pt-16">
        <div class="grid grid-cols-2 gap-10 px-10">
            <div class="text-center"><div class="w-full border-b border-black mb-2"></div><div class="text-[9px] font-black uppercase">Prepared By</div></div>
            <div class="text-center"><div class="w-full border-b border-black mb-2"></div><div class="text-[9px] font-black uppercase">Authorized By</div></div>
        </div>
    </div>
@endsection
