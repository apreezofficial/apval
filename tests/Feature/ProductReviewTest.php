<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendor;
    private Product $product;
    private OrderItem $orderItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();
        $this->vendor = User::factory()->create(['role' => 'vendor']);

        $this->product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name'      => 'Fresh Cassava Tubers',
            'price'     => 1500,
        ]);

        $order = Order::create([
            'user_id'        => $this->buyer->id,
            'total_amount'   => 1500,
            'status'         => 'completed',
            'payment_status' => 'escrow_released',
        ]);

        $this->orderItem = OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $this->product->id,
            'vendor_id'      => $this->vendor->id,
            'quantity'       => 1,
            'unit_price'     => 1500,
            'total_price'    => 1500,
            'vendor_status'  => 'delivered',
        ]);
    }

    public function test_buyer_can_review_delivered_order_item()
    {
        $response = $this->actingAs($this->buyer)
            ->postJson("/api/commerce/orders/items/{$this->orderItem->id}/reviews", [
                'rating'  => 5,
                'comment' => 'Excellent fresh tubers! Super fast delivery.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Review submitted successfully.',
                'data'    => [
                    'rating'  => 5,
                    'comment' => 'Excellent fresh tubers! Super fast delivery.',
                ],
            ]);

        $this->assertDatabaseHas('product_reviews', [
            'order_item_id' => $this->orderItem->id,
            'product_id'    => $this->product->id,
            'user_id'       => $this->buyer->id,
            'rating'        => 5,
        ]);
    }

    public function test_buyer_cannot_review_non_delivered_item()
    {
        $this->orderItem->update(['vendor_status' => 'dispatched']);

        $response = $this->actingAs($this->buyer)
            ->postJson("/api/commerce/orders/items/{$this->orderItem->id}/reviews", [
                'rating' => 4,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status'  => 'error',
                'message' => 'You can only review items that have been delivered.',
            ]);
    }

    public function test_buyer_cannot_submit_duplicate_review()
    {
        ProductReview::create([
            'order_item_id' => $this->orderItem->id,
            'product_id'    => $this->product->id,
            'user_id'       => $this->buyer->id,
            'rating'        => 5,
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson("/api/commerce/orders/items/{$this->orderItem->id}/reviews", [
                'rating' => 4,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status'  => 'error',
                'message' => 'You have already submitted a review for this item.',
            ]);
    }

    public function test_public_user_can_view_product_reviews_and_summary()
    {
        ProductReview::create([
            'order_item_id' => $this->orderItem->id,
            'product_id'    => $this->product->id,
            'user_id'       => $this->buyer->id,
            'rating'        => 5,
            'comment'       => 'Great quality',
        ]);

        $response = $this->getJson("/api/commerce/marketplace/products/{$this->product->id}/reviews");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'product_id'     => $this->product->id,
                    'average_rating' => 5.0,
                    'total_reviews'  => 1,
                    'rating_breakdown' => [
                        '5_star' => 1,
                        '4_star' => 0,
                    ],
                ],
            ]);
    }
}
