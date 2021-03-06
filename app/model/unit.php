<?php

class unit implements unitInterface
{
    // technical
    protected $_type;

    // predefined
    /** @var string */
    protected $title;
    /** @var string */
    protected $path;
    /** @var ?array */
    protected $data;


    /**
     * @param string $title
     * @throws Exception
     */
    public function __construct(string $title)
    {
        $this->setTitle($title);
        $this->setPath();
    }


    /** @param string $title */
    private function setTitle(string $title)
    {
        $this->title = $title;
    }

    /** @return string */
    public function getTitle(): string
    {
        return $this->title;
    }

    /** @throws Exception if unit type is absent by specified path */
    protected function setPath()
    {
        $path = bendSeparatorsRight($this->path);
        if ((($this->_type === 'dir') && !is_dir($path)) || ($this->_type === 'file' && !is_file($path))) {
            throw new Exception(prepareIssueCard($this->_type . ' is absent.', $path));
        }

        $this->path = $path;
    }

    /** @return string */
    public function getPath(): string
    {
        return $this->path;
    }

    /** @return array|null */
    public function getData(): ?array
    {
        return $this->data;
    }


    /**
     * @param array $data
     * @return string
     */
    protected function prepareTagsString(array $data): string
    {
        if (!array_key_exists('tags', $data)) return '';

        $delimiters = settings::getInstance()->get('delimiters');

        $result = $delimiters['section'];
        foreach ($data['tags'] as $k => $v) {
            $v = implode($delimiters['tag_info'], $v);

            if ($k === 'info') {
                $result .= $delimiters['tag_open'] . "$v" . $delimiters['tag_close'];
            } else {
                $result .= $delimiters['tag_open'] . "$k" . $delimiters['tag_name'] . "$v" . $delimiters['tag_close'];
            }
        }

        return $result;
    }

    /**
     * @param string $tagsSection
     */
    protected function setTags(string $tagsSection)
    {
        $delimiters = settings::getInstance()->get('delimiters');

        $tags = substr($tagsSection, 1, -1);
        $tags = explode($delimiters['tag_close'] . $delimiters['tag_open'], $tags);
        foreach ($tags as $tag) {
            if (strpos($tag, $delimiters['tag_name'])) {
                $tag = explode($delimiters['tag_name'], $tag);
                $this->data['tags'][$tag[0]] = explode($delimiters['tag_info'], $tag[1]);
            } else {
                $this->data['tags']['info'] = explode($delimiters['tag_info'], $tag);
            }
        }
    }

    /**
     * @param string $string
     * @param string|null $pattern
     * @throws Exception
     */
    protected function verifyFileName(string $string, ?string $pattern = null)
    {
        if ($pattern) {
            preg_match($pattern, $string, $matches);
            if (empty($matches) || $matches[0] !== $string) {
                $err = sprintf(
                    "Invalid %s filename format. Format it to match the %s pattern.",
                    ucwords(get_class($this)),
                    $pattern
                );
                throw new Exception(prepareIssueCard($err, $this->getPath()));
            }
        }
    }

    /**
     * @param string $string
     * @return bool
     */
    protected function isMarkedToBeUpdated(string $string): bool
    {
        return isMarkedWithPrefix($string, settings::getInstance()->get('tags/update_metadata'));
    }

    /**
     * @param string $string
     * @return string
     */
    protected function adjustName(string $string): string
    {
        $updatePrefixMark = settings::getInstance()->get('tags/update_metadata');
        if ($this->isMarkedToBeUpdated($string)) {
            $string = substr($string, strlen($updatePrefixMark));
        }

        return $string;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function renameUpdated(): bool
    {
        try {
            $result = chmod($this->getPath(), 0777);
        } catch (Exception $e) {
            $err = prepareIssueCard('Permissions providing is failed.', $this->getPath());
            echo $err, "\n\n", $e->getMessage();
        }

        try {
            $result = rename(
                $this->getPath(),
                str_replace($this->getTitle(), $this->adjustName($this->getTitle()), $this->getPath())
            );
        } catch (Exception $e) {
            $err = prepareIssueCard('Renaming is failed.', $this->getPath());
            echo $err, "\n\n", $e->getMessage();
        }

        return $result;
    }
}
