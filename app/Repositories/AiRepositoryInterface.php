<?php

namespace App\Repositories;

interface AiRepositoryInterface
{
    /**
     * Generate AI suggest message for a specific company and category,
     * with optional delegate and product context.
     *
     * @param int $user_id ID of the user (pengirim)
     * @param string $category Category name (e.g. "Suggest Meeting")
     * @param int $company_id Target company ID
     * @param int|null $delegate_id Optional delegate ID
     * @param int|null $product_id Optional product ID
     * @return string|array
     */
    public function getSuggestMeet($user_id, $category, $company_id, $delegate_id = null, $product_id = null);

    /**
     * Placeholder for future AI room chat logic
     */
    public function getRoomChat();
}
