<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Modules\ASCM\Models\WarrantyClaim;
use App\Modules\ASCM\Models\WarrantyClaimDocument;
use App\Modules\ASCM\Models\WarrantyClaimNote;
use App\Modules\ASCM\Models\WarrantyRegistration;
use App\Modules\ASCM\Models\WarrantyRepair;
use Illuminate\Database\Seeder;

class WarrantySeeder extends Seeder
{
    /**
     * Same note as CaseManagementSeeder — these counts are illustrative,
     * not read from the UI's stat cards.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $staff = User::all();

        $counts = [
            'submitted' => 4,
            'under_review' => 8,
            'approved' => 5,
            'rejected' => 3,
        ];

        foreach ($counts as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $registration = WarrantyRegistration::factory()->create([
                    'customer_id' => $customers->random()->customer_id,
                    'product_id' => $products->random()->id,
                ]);

                $claim = WarrantyClaim::factory()
                    ->status($status)
                    ->create([
                        'warranty_registration_id' => $registration->id,
                        'customer_id' => $registration->customer_id,
                        'decision_by' => in_array($status, ['approved', 'rejected']) && $staff->isNotEmpty()
                            ? $staff->random()->id
                            : null,
                    ]);

                $this->seedNotesDocumentsAndRepairs($claim, $staff);
            }
        }
    }

    private function seedNotesDocumentsAndRepairs(WarrantyClaim $claim, $staff): void
    {
        WarrantyClaimNote::create([
            'warranty_claim_id' => $claim->id,
            'author_id' => $staff->isNotEmpty() ? $staff->random()->id : null,
            'note_type' => 'general',
            'body' => fake()->sentence(12),
        ]);

        if (in_array($claim->status, ['approved', 'rejected'])) {
            WarrantyClaimNote::create([
                'warranty_claim_id' => $claim->id,
                'author_id' => $staff->isNotEmpty() ? $staff->random()->id : null,
                'note_type' => 'decision',
                'body' => $claim->status === 'approved'
                    ? 'Approved after inspection confirmed manufacturing defect.'
                    : 'Rejected — damage found to be outside coverage terms.',
            ]);
        }

        if (fake()->boolean(70)) {
            WarrantyClaimDocument::create([
                'warranty_claim_id' => $claim->id,
                'uploaded_by' => $staff->isNotEmpty() ? $staff->random()->id : null,
                'file_name' => fake()->randomElement(['proof_of_purchase.pdf', 'inspection_photo.jpg', 'rma_form.pdf']),
                'file_path' => 'warranty-documents/' . fake()->uuid() . '.dat',
                'file_size' => fake()->numberBetween(20_000, 3_000_000),
                'mime_type' => fake()->randomElement(['application/pdf', 'image/jpeg']),
            ]);
        }

        if ($claim->status === 'approved' && fake()->boolean(60)) {
            WarrantyRepair::create([
                'warranty_claim_id' => $claim->id,
                'status' => fake()->randomElement(['scheduled', 'in_progress', 'completed']),
                'technician_id' => $staff->isNotEmpty() ? $staff->random()->id : null,
                'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
                'notes' => fake()->boolean(50) ? fake()->sentence(8) : null,
            ]);
        }
    }
}
