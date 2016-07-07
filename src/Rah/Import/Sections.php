<?php

/**
 * Imports sections.
 */

class rah_flat_Import_Sections extends rah_flat_Import_Pages
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'section';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_section';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        $sql = array();
        $where = "name = '".doSlash($file->getTemplateName())."'";

        foreach ($file->getTemplateJSONContents() as $key => $value) {
            if ($key !== 'name' && in_array(strtolower((string) $key), $this->getTableColumns(), true)) {
                $sql[] = $this->formatStatement($key, $value);
            }
        }

        return $sql && safe_upsert($this->getTableName(), implode(',', $sql), $where);
    }

    /**
     * Formats a SQL insert statement value.
     *
     * @param  string $field The field
     * @param  string $value The value
     * @return string
     */

    protected function formatStatement($field, $value)
    {
        if ($value === null) {
            return "`{$field}` = NULL";
        }

        if (is_bool($value) || is_int($value)) {
            return "`{$field}` = ".intval($value);
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return "`{$field}` = '".doSlash((string) $value)."'";
    }
}
