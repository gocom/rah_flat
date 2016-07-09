<?php

/**
 * Imports Textpacks.
 */

class rah_flat_Import_Textpacks extends rah_flat_Import_Sections
{

    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'lang';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_lang';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {

        $sql = array();
        $where = "lang = '".doSlash($file->getTemplateName())."'";

        foreach ($file->getTemplateJSONContents() as $event) {
            foreach ($event as $key => $value) {
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

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        $path = explode('/', $this->directory);

        if ($name) {
            safe_delete($this->getTableName(), 'event = "'.doSlash($path[2]).'" && owner = "rah_flat_lang" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'event = "'.doSlash($path[2]).'" && owner = "rah_flat_lang"');
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}
