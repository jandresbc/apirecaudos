<?php

namespace App\EventListener;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use Overblog\GraphQLBundle\Event\ExecutorResultEvent;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;

class CustomErrorHandler
{
  public function onPostExecutor(ExecutorResultEvent $event)
  {
      $myErrorFormatter = function(Error $error) {
          return FormattedError::createFromException($error);
      };

      $myErrorHandler = function(array $errors, callable $formatter) {
          return array_map($formatter, $errors);
      };

      $event->getResult()
          ->setErrorFormatter($myErrorFormatter)
          ->setErrorsHandler($myErrorHandler);
  }

  public function onErrorFormatting(ErrorFormattingEvent $event)
  {
      $error = $event->getError();
      if ($error->getPrevious()) {
          $code = $error->getPrevious()->getCode();
      } else {
          $code = $error->getCode();
      }
      $formattedError = $event->getFormattedError();
      //$formattedError->offsetSet('code', $code); 
      $formattedError['code'] = $code;
  }
}