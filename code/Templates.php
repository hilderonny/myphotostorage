<?php

/*
 * The MIT License
 *
 * Copyright 2014 Ronny Hildebrandt <ronny.hildebrandt@avorium.de>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Provides static functions for handling HTML-PHP templates and their
 * localization. Templates lie in the /templates folder and have the file
 * names TEMPLATENAME.phtml. Translation templates have the name
 * TEMPLATENAME.xy.phtml where XY ist the two digit language code in lower
 * case. The system searches for a localized template for the current user
 * first. When it find a matchin localized template, this one is used.
 * Otherwise the template without the language code is used.
 *
 * @author Ronny Hildebrandt <ronny.hildebrandt@avorium.de>
 */
class Templates {
    
    /**
     * Includes the template with the given name (translated one, if it exists.
     * 
     * @param string $templatename Name of the template without extension.
     */
    static function includeTemplate($templatename) {
        $preferredlanguage = Localization::getPreferredVisitorLanguage();
        $localizedfilename = 'templates/'.$templatename.$preferredlanguage.'.phtml';
        $fallbackfilename = 'templates/'.$templatename.'.phtml';
        if (file_exists($localizedfilename)) {
            include $localizedfilename;
        } else {
            include $fallbackfilename;
        }
    }
}
