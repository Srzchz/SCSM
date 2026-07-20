<?php

namespace App\Http\Controllers;

class HubController extends Controller
{
    public function index()
    {
        return view('hub', [
            'modules' => [
                [
                    'name' => 'After-Sales Support & Case Management',
                    'abbr' => 'ASCM',
                    'description' => 'Case logging, assignment, resolution tracking, and warranty/service contract claims.',
                    'color' => '#6C5CE7',
                    'links' => [
                        ['label' => 'Dashboard (Cases & Warranty)', 'route' => 'ascm.dashboard'],
                    ],
                ],
                [
                    'name' => 'Sales Order Management',
                    'abbr' => 'SOM',
                    'description' => 'Quotations, sales orders, invoices, and pricing rules.',
                    'color' => '#00B894',
                    'links' => [
                        ['label' => 'Sales Order Workspace', 'route' => 'sales-order-management.index'],
                    ],
                ],
                [
                    'name' => 'Customer Relationship Management',
                    'abbr' => 'CRM',
                    'description' => 'Customer profiles, order history, communication logs, and purchase behavior insights.',
                    'color' => '#0984E3',
                    'links' => [
                        ['label' => 'CRM Dashboard', 'route' => 'crm.dashboard'],
                        ['label' => 'Customers', 'route' => 'customers.index'],
                        ['label' => 'Communication Logs', 'route' => 'communication-logs'],
                        ['label' => 'Order History', 'route' => 'orders.index'],
                        ['label' => 'Purchase Behavior', 'route' => 'purchase-behavior'],
                    ],
                ],
                [
                    'name' => 'Sales Performance Reporting',
                    'abbr' => 'SPR',
                    'description' => 'Rep/region/product targets, revenue forecasting, and performance alerts.',
                    'color' => '#E17055',
                    'links' => [
                        ['label' => 'Reporting Dashboard', 'route' => 'sales-performance-reporting.dashboard'],
                    ],
                ],
            ],
        ]);
    }
}
