Feature: TinCan Statements Update Product Completeness, based on Courses in the Product
Scenario (LMS Side):
  Given a single course is complete (LMS Side)
  Then a TinCan statement will be created like this:
    [object][definition][type] = course
    [result][completion] = true
    [context][contextActivities][grouping][extension] = { json completeness object }

Scenario:
   Given the above LRS statement is being recievied (OKM / LRS side)
   When there is no [context][contextActivities][grouping]
   Then the product is complete

Scenario:
   Given the above LRS statement is being recievied (OKM / LRS side)
   When the [context][contextActivities][grouping][id] === [object][id]
   Then the product is complete

Scenario:
   Given the above LRS statement is being recievied (OKM / LRS side)
   Given there is a [context][contextActivities][grouping]
   And the [context][contextActivities][grouping][id] !== [object][id]
   And the { json completeness object } shows incomplete courses
   Then the product is not complete

Scenario:
   Given the above LRS statement is being recievied (OKM / LRS side)
   Given there is a [context][contextActivities][grouping]
   And the [context][contextActivities][grouping][id] !== [object][id]
   And the { json completeness object } shows ALL complete courses
   Then the product is complete
