<?php
/**
 *
 * @author sirshurf
 * @version 
 */
/**
 * Menu helper
 *
 * @uses viewHelper Labadmin_View_Helper_Navigation
 */
class Bf_View_Helper_Menu extends Zend_View_Helper_Navigation_Menu
{
    /**
     * Determines whether a page should be accepted by ACL when iterating
     *
     * Rules:
     * - If helper has no ACL, page is accepted
     * - If page has a resource or privilege defined, page is accepted
     * if the ACL allows access to it using the helper's role
     * - If page has no resource or privilege, page is accepted
     *
     * @param  Zend_Navigation_Page $page  page to check
     * @return bool                        whether page is accepted by ACL
     */
    protected function _acceptAcl (Zend_Navigation_Page $page)
    {
        if (! $acl = $this->getAcl()) {
            // no acl registered means don't use acl
            return true;
        }
        $resource = $page->getResource();
        $privilege = $page->getPrivilege();
        if ($resource) {
            return $this->_acl->checkPermissionsById($resource, $privilege);
        }
        return true;
    }
    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container   container to render
     * @param  string                    $ulClass     CSS class for first UL
     * @param  string                    $indent      initial indentation
     * @param  int|null                  $minDepth    minimum depth
     * @param  int|null                  $maxDepth    maximum depth
     * @param  bool                      $onlyActive  render only active branch?
     * @return string
     */
    protected function _renderMenu (Zend_Navigation_Container $container, 
    $ulClass, $indent, $minDepth, $maxDepth, $onlyActive)
    {
        $html = '';
        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);
        if ($found) {
            $foundPage = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }
        // create iterator
        $iterator = new RecursiveIteratorIterator($container, 
        RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }
        // iterate container
        $prevDepth = - 1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || ! $this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            } else 
                if ($onlyActive && ! $isActive) {
                    // page is not active itself, but might be in the active branch
                    $accept = false;
                    if ($foundPage) {
                        if ($foundPage->hasPage($page)) {
                            // accept if page is a direct child of the active page
                            $accept = true;
                        } else 
                            if ($foundPage->getParent()->hasPage($page)) {
                                // page is a sibling of the active page...
                                if (! $foundPage->hasPages() ||
                                 is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                                    // accept if active page has no children, or the
                                    // children are too deep to be rendered
                                    $accept = true;
                                }
                            }
                    }
                    if (! $accept) {
                        continue;
                    }
                }
            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);
            if ($depth > $prevDepth) {
                // start new ul tag
                if ($ulClass && $depth == 0) {
                    $ulClass = ' id="hmenu"';
                } else {
                    $ulClass = ' class="drop"';
                }
                $html .= $myIndent . '<ul' . $ulClass . '>' . self::EOL;
            } else 
                if ($prevDepth > $depth) {
                    // close li/ul tags until we're at current depth
                    for ($i = $prevDepth; $i > $depth; $i --) {
                        $ind = $indent . str_repeat('        ', $i);
                        $html .= $ind . '    </li>' . self::EOL;
                        $html .= $ind . '</ul>' . self::EOL;
                    }
                    // close previous li tag
                    if ($depth == 0) {
                        $html .= $myIndent . '    </div></div></div></li>' .
                         self::EOL;
                    } else {
                        $html .= $myIndent . '    </li>' . self::EOL;
                    }
                } else {
                    // close previous li tag
                    if ($depth == 0) {
                        $html .= $myIndent . '    </div></div></div></li>' .
                         self::EOL;
                    } else {
                        $html .= $myIndent . '    </li>' . self::EOL;
                    }
                }
            // render li tag and page
            if ($depth == 0) {
                $liClass = $isActive ? ' class="selected"' : ' class="regular"';
                if (! $isActive && $page->css) {
                    $liClass = ' class="' . $page->css . '"';
                }
            } else {
                $liClass = "";
            }
            $html .= $myIndent . '    <li' . $liClass . '>' . self::EOL;
            if ($depth == 0) {
                $html .= '<div class="right"><div class="left"><div class="back">' .
                 self::EOL;
                $page->setClass('top');
            }
            $html .= $myIndent . '        ' . $this->htmlify($page) . self::EOL;
            // store as previous depth for next iteration
            $prevDepth = $depth;
        }
        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth + 1; $i > 0; $i --) {
                $myIndent = $indent . str_repeat('        ', $i - 1);
                if ($i == 1) {
                    $html .= $myIndent . '</div></div></div></li>' . self::EOL .
                     $myIndent . '</ul><br class="clear" />' . self::EOL;
                } else {
                    $html .= $myIndent . '    </li>' . self::EOL . $myIndent .
                     '</ul>' . self::EOL;
                }
            }
            $html = rtrim($html, self::EOL);
        }
        return $html;
    }
    /**
     * Renders the deepest active menu within [$minDepth, $maxDeth], (called
     * from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container  container to render
     * @param  array                     $active     active page and depth
     * @param  string                    $ulClass    CSS class for first UL
     * @param  string                    $indent     initial indentation
     * @param  int|null                  $minDepth   minimum depth
     * @param  int|null                  $maxDepth   maximum depth
     * @return string                                rendered menu
     */
    protected function _renderDeepestMenu (Zend_Navigation_Container $container, 
    $ulClass, $indent, $minDepth, $maxDepth)
    {
        if (! $active = $this->findActive($container, $minDepth - 1, $maxDepth)) {
            return '';
        }
        // special case if active page is one below minDepth
        if ($active['depth'] < $minDepth) {
            if (! $active['page']->hasPages()) {
                return '';
            }
        } else 
            if (! $active['page']->hasPages()) {
                // found pages has no children; render siblings
                $active['page'] = $active['page']->getParent();
            } else 
                if (is_int($maxDepth) && $active['depth'] + 1 > $maxDepth) {
                    // children are below max depth; render siblings
                    $active['page'] = $active['page']->getParent();
                }
        $html = "";
        //  $ulClass = $ulClass ? ' class="' . $ulClass . '"' : '';
        //  $html = $indent . '<ul' . $ulClass . '>' . self::EOL;
        
        foreach ($active['page'] as $subPage) {
            if (! $this->accept($subPage)) {
                continue;
            }
            $html .= $indent. '<div class="'.($subPage->isActive(true) ? 'selected' : 'regular').'">
			<div class="left">
				<div class="right">
					<div class="back">';
            //      $liClass = $subPage->isActive(true) ? ' class="active"' : '';
            //    $html .= $indent . '    <li' . $liClass . '>' . self::EOL;
            $html .= $indent . '        ' .
             $this->subHtmlify($subPage) . self::EOL;
             //    $html .= $indent . '    </li>' . self::EOL;
             $html .= $indent.'</div>
				</div>
			</div>
		</div>';
             
        }
        //$html .= $indent . '</ul>';
        return $html;
    }
    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty
     *
     * Overrides {@link Zend_View_Helper_Navigation_Abstract::htmlify()}.
     *
     * @param  Zend_Navigation_Page $page  page to generate HTML for
     * @return string                      HTML string for the given page
     */
    public function subHtmlify (Zend_Navigation_Page $page)
    {
        // get label and title for translating
        $label = $page->getLabel();
        $title = $page->getTitle();
        // translate label and title?
        if ($this->getUseTranslator() && $t = $this->getTranslator()) {
            if (is_string($label) && ! empty($label)) {
                $label = $t->translate($label);
            }
            if (is_string($title) && ! empty($title)) {
                $title = $t->translate($title);
            }
        }
        // get attribs for element
        $attribs = array('id' => $page->getId(), 'title' => $title, 
        'class' => $page->getClass());
        // does page have a href?
        $href = $page->getHref();
        if (! $page->isActive(true)) {
            $element = 'a';
            $attribs['href'] = $href;
            $attribs['target'] = $page->getTarget();
        } else {
            $element = 'a';
            $attribs['href'] = $href;
            $attribs['target'] = $page->getTarget();
            $attribs['class'] = "active";
        }
        // else {
        //    $element = 'span';
        //}
        return '<' . $element .
         $this->_htmlAttribs($attribs) . '>' . $this->view->escape($label) . '</' .
         $element . '>';
    }
}

