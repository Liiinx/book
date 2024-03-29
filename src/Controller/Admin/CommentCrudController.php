<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }


    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('conference'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('conference');
        yield TextField::new('author');
        yield EmailField::new('email');
        yield TextareaField::new('text')
            ->hideOnIndex()
        ;
        yield ImageField::new('photoFilename')
            ->setBasePath('/uploads/photos')
            ->setLabel('Photo')
            ->onlyOnIndex()
        ;
//        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
//            'years' => range(date('Y'), date('Y') + 5),
//            'widget' => 'single_text',
//        ]);
//        if (Crud::PAGE_EDIT === $pageName) {
//            yield $createdAt->setFormTypeOption('disabled', true);
//        }
        yield DateTimeField::new('createdAt');
        yield TextField::new('state');
    }
}
