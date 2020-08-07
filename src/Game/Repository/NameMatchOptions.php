<?php

namespace Game\Repository;


class NameMatchOptions
{
    private float $max_length_diff_per_letter;
    private float $max_errors_per_letter;

    public function __construct(float $max_length_diff_per_letter,
                                float $max_errors_per_letter)
    {
        assert($max_length_diff_per_letter >= 0);
        assert($max_length_diff_per_letter <= 1);

        assert($max_errors_per_letter >= 0);
        assert($max_errors_per_letter <= 1);

        $this->max_length_diff_per_letter = $max_length_diff_per_letter;
        $this->max_errors_per_letter = $max_errors_per_letter;
    }

    public function max_length_diff_per_letter() { return $this->max_length_diff_per_letter; }
    public function max_errors_per_letter() { return $this->max_errors_per_letter; }
}
