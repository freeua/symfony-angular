<?php

namespace AppBundle\Repository;

/**
 * HideJobRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class HideJobRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $candidateID
     * @param array $params
     * @return mixed
     */
    public function getDeclineJobsForCandidate($candidateID, $params=array()){
        $query = $this->createQueryBuilder('hj')
            ->from('AppBundle:Job', 'j')
            ->select('j.id, j.jobTitle, j.industry, j.roleDescriptionChange as roleDescription, j.companyAddress, j.addressCity, j.closureDate as endDate, j.started as createdDate')
            ->where('hj.job = j.id')
            ->andWhere('hj.user = :candidateID')
            ->setParameter('candidateID', $candidateID)
            ->andWhere('j.status = :status')
            ->setParameter('status', true)
            ->andWhere('j.approve = :approve')
            ->setParameter('approve', true)
        ;

        if(isset($params['startDate']) && !empty($params['startDate']) && $params['startDate'] != 'null'){
            $date = ($params['startDate'] instanceof \DateTime) ? $params['startDate'] : new \DateTime($params['startDate']);
            if($date instanceof \DateTime){
                $query->andWhere("DATE_FORMAT(j.created, '%Y-%m-%d') >= :startDate")
                    ->setParameter('startDate', $date->format('Y-m-d'));
            }
        }

        if(isset($params['endDate']) && !empty($params['endDate']) && $params['endDate'] != 'null'){
            $date = ($params['endDate'] instanceof \DateTime) ? $params['endDate'] : new \DateTime($params['endDate']);
            if($date instanceof \DateTime){
                $query->andWhere("DATE_FORMAT(j.created, '%Y-%m-%d') <= :endDate")
                    ->setParameter('endDate', $date->format('Y-m-d'));
            }
        }

        $query->orderBy('hj.id', 'DESC');


        return $query->getQuery()->getResult();
    }

    /**
     * @param $candidateID
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountDeclineJobsForCandidate($candidateID){
        $query = $this->createQueryBuilder('hj')
            ->from('AppBundle:Job', 'j')
            ->select('count(hj.id) as declineCount')
            ->where('hj.job = j.id')
            ->andWhere('hj.user = :candidateID')
            ->setParameter('candidateID', $candidateID)
            ->andWhere('j.status = :status')
            ->setParameter('status', true)
            ->andWhere('j.approve = :approve')
            ->setParameter('approve', true)
        ;


        return $query->getQuery()->getOneOrNullResult();
    }
}