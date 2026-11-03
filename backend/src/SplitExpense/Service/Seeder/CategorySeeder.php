<?php

namespace App\SplitExpense\Service\Seeder;

use App\SplitExpense\Entity\Category;
use App\SplitExpense\Repository\CategoryRepository;

readonly class CategorySeeder
{
    public const string DEFAULT_TAG = 'other';

    private const array TAGS = [
        'bills',
        'education',
        'entertainment',
        'gifts',
        'groceries',
        'health',
        'hobby',
        'household',
        'other',
        'rent',
        'restaurants',
        'shopping',
        'sport',
        'subscriptions',
        'transport',
        'travel',
    ];

    public function __construct(
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function seed(): void
    {
        $existingTags = [];
        foreach ($this->categoryRepository->findAll() as $category) {
            $existingTags[$category->getTag()] = true;
        }

        foreach (self::TAGS as $tag) {
            if (isset($existingTags[$tag])) {
                continue;
            }

            $this->categoryRepository->save(new Category(
                tag: $tag,
                title: ucfirst($tag),
            ));
        }

        $this->categoryRepository->flush();
    }
}
